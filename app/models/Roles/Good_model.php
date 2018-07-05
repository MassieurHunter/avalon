<?php

class Good_model extends Role_model
{

	/**
	 * @return bool
	 */
	public function isSeenByEvil(): boolean {
		return false;
	}

	/**
	 * @return bool
	 */
	public function isSeenByMerlin(): boolean {
		return false;
	}

	/**
	 * @return bool
	 */
	public function isSeenByPerceval(): boolean {
		return false;
	}

	/**
	 * @return bool
	 */
	public function canKillMerlin(): boolean {
		return false;
	}

}