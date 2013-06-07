<?php
interface IBillingSystem {
	/*
	* Method to connect to remote billing system db
	*/
	public function connect($host,$databsae,$username,$password);
	/*
	* Method to list users from billing system
	*	@param	bool	active		If false, lists all clients; If true, lists active clients
	*	@return	array				Array of users
	*/
	public function usersList($active=true);
}
?>