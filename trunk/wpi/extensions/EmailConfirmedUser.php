<?php

//Register a hook that passes new users 
//when they successfully confirm by email

//NOTE: this hook is available from v1.16.0

$wgHooks['ConfirmEmailComplete'][] = 'wfEmailConfirmedUser';

function wfEmailConfirmedUser($user) {
        $user->addGroup('webservice');
        return true;
}
