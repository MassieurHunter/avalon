import PlayerModel from '../../dev/js/models/PlayerModel';
import RoleModel from '../../dev/js/models/RoleModel';
import GameModel from '../../dev/js/models/GameModel';
import https from 'https';
import fs from 'fs';

let ssl_options = {
	key: fs.readFileSync('/etc/nginx/ssl/avalon.local.key'),
	cert: fs.readFileSync('/etc/nginx/ssl/avalon.local.crt')
};

let server = https.createServer(ssl_options);
let io = require('socket.io').listen(server);
let gamesPlayersForStarting = {};
let gamesPlayersVotedForTeam = {};
let gamesPlayersVotedForQuest = {};
let gamesPlayersWithRoles = {};
let gamesSockets = {};
let gamesProgress = {};

const {exec} = require('child_process');

io.sockets.on('connection', (socket) => {

	socket.emit('message', {
		type: 'connection',
		id: socket.id
	});

	socket.on('playerJoined', (data) => {

		let Player = new PlayerModel(data.player);
		let Game = new GameModel(data.game);
		let roomUid = 'game' + Game.getCode();

		if (!gamesSockets.hasOwnProperty(roomUid)) {
			gamesSockets[roomUid] = [];
		}

		socket.join(roomUid);

		if (gamesSockets[roomUid].hasOwnProperty(Player.getPlayerUid())) {

			let oldSocket = gamesSockets[roomUid][Player.getPlayerUid()];
			oldSocket.leave(roomUid);
			oldSocket.emit('message', {
				type: 'connectedElseWhere',
				player: Player.toJSON(),
				game: Game.toJSON()
			});

			console.log('player ' + Player.getName() + ' re-joined the game with code ' + Game.getCode());

			gamesSockets[roomUid][Player.getPlayerUid()] = socket;

			if (gamesPlayersForStarting.hasOwnProperty(roomUid)) {
				
				let oldGamesPlayersForStarting = gamesPlayersForStarting[roomUid];
				gamesPlayersForStarting[roomUid] = [];

				for (let loopPlayer of oldGamesPlayersForStarting) {

					if (loopPlayer.getPlayerUid() !== Player.getPlayerUid()) {

						gamesPlayersForStarting[roomUid].push(loopPlayer);

					}

				}
				
			}


			io.in(roomUid).emit('message', {
				type: 'playerRejoined',
				player: Player.toJSON(),
				game: Game.toJSON()
			});


		} else {

			console.log('player ' + Player.getName() + ' joined the game with code ' + Game.getCode());

			gamesSockets[roomUid][Player.getPlayerUid()] = socket;

			io.in(roomUid).emit('message', {
				type: 'playerJoined',
				player: Player.toJSON(),
				game: Game.toJSON()
			});

		}

	});

	socket.on('playerRejoined', (data) => {

		let Game = new GameModel(data.game);
		let roomUid = 'game' + Game.getCode();
		let Player = new PlayerModel(data.player);

		if (gamesProgress.hasOwnProperty(roomUid)) {

			gamesSockets[roomUid][Player.getPlayerUid()].emit('message', {
				type: 'rolesInfos',
				game: Game.toJSON()
			});

		}

	});

	socket.on('gameStart', (data) => {

		let Player = new PlayerModel(data.player);
		let Game = new GameModel(data.game);
		let roomUid = 'game' + Game.getCode();

		console.log('game start message recieved');


		if (!gamesPlayersForStarting.hasOwnProperty(roomUid)) {
			gamesPlayersForStarting[roomUid] = [];
		}

		let playerPresent = false;

		for (let player of gamesPlayersForStarting[roomUid]) {

			if (player.getPlayerUid() === Player.getPlayerUid()) {

				playerPresent = true;

			}

		}

		if (!playerPresent) {

			gamesPlayersForStarting[roomUid].push(Player);

			if (gamesPlayersForStarting[roomUid].length === Game.getMaxPlayers()) {

				if (!gamesProgress.hasOwnProperty(roomUid)) {

					gamesProgress[roomUid] = {
						progress: 0,
						currentRole: new RoleModel(),
					};

				}

				console.log('Starting game ' + Game.getCode());

				let randomPlayer = gamesPlayersForStarting[roomUid][Math.floor(Math.random() * gamesPlayersForStarting[roomUid].length)];
				let randomPlayerUid = randomPlayer.getPlayerUid();

				console.log(randomPlayer.getName() + ' has been chosen to start the game');
				
				gamesSockets[roomUid][randomPlayerUid].emit('message', {
					type: 'gameStart',
				});

			}

		}

	});

	socket.on('rolesInfos', (data) => {

		let Game = new GameModel(data.game);
		let roomUid = 'game' + Game.getCode();

		io.in(roomUid).emit('message', {
			type: 'rolesInfos',
			game: Game.toJSON()
		});

	});

	socket.on('playerProposedTeam', (data) => {

		let Player = new PlayerModel(data.player);
		let Game = new GameModel(data.game);
		let Team = new TeamModel(data.team);
		let roomUid = 'game' + Game.getCode();

		console.log('player ' + Player.getName() + ' has proposed Team : ' + Team.getPlayersList());



	});

	socket.on('playerVotedForTeam', (data) => {

		let Player = new PlayerModel(data.player);
		let Game = new GameModel(data.game);
    let Team = new TeamModel(data.team);
		let roomUid = 'game' + Game.getCode();

		console.log('player ' + Player.getName() + ' has voted');

		if (!gamesPlayersVotedForTeam.hasOwnProperty(roomUid)) {
			gamesPlayersVotedForTeam[roomUid] = [];
		}

		let alreadyVoted = false;

		for (let loopPlayer of gamesPlayersVotedForTeam[roomUid]) {

			if (loopPlayer.getPlayerUid() === Player.getPlayerUid()) {

				alreadyVoted = true;

			}

		}

		if (!alreadyVoted) {

			gamesPlayersVotedForTeam[roomUid].push(Player);

		}

		io.in(roomUid).emit('message', {
			type: 'playerVoted',
			nbVotes: gamesPlayersVotedForTeam[roomUid].length
		});

		if (gamesPlayersVotedForTeam[roomUid].length === gamesPlayersWithRoles[roomUid].length) {

			//reset votes

		}


	});

	socket.on('disconnect', () => {

		console.log("user " + socket.id + " disconnected")

	});

});

server.listen(3002, () => {
	console.log('listening on *:3002');
});