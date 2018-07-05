<?php

class Evil_model extends Role_model
{

	/**
	 * @return bool
	 */
	public function isSeenByEvil(): boolean {
		return true;
	}

	/**
	 * @return bool
	 */
	public function isSeenByMerlin(): boolean {
		return true;
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