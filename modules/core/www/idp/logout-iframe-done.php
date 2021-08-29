<?php

use SimpleSAML\Auth;
use SimpleSAML\Configuration;
use SimpleSAML\Error;
use SimpleSAML\IdP;
use SimpleSAML\Stats;

if (!isset($_REQUEST['id'])) {
    throw new Error\BadRequest('Missing required parameter: id');
}

/** @psalm-var array $state */
$state = Auth\State::loadState($_REQUEST['id'], 'core:Logout-IFrame');
$idp = IdP::getByState($state);

$associations = $idp->getAssociations();

$logger = Configuration::getInstance()::getLogger();
if (!isset($_REQUEST['cancel'])) {
    $logger->stats('slo-iframe done');
    Stats::log('core:idp:logout-iframe:page', ['type' => 'done']);
    $SPs = $state['core:Logout-IFrame:Associations'];
} else {
    // user skipped global logout
    $logger->stats('slo-iframe skip');
    Stats::log('core:idp:logout-iframe:page', ['type' => 'skip']);
    $SPs = []; // no SPs should have been logged out
    $state['core:Failed'] = true; // mark as partial logout
}

// find the status of all SPs
foreach ($SPs as $assocId => &$sp) {
    $spId = 'logout-iframe-' . sha1($assocId);

    if (isset($_REQUEST[$spId])) {
        $spStatus = $_REQUEST[$spId];
        if ($spStatus === 'completed' || $spStatus === 'failed') {
            $sp['core:Logout-IFrame:State'] = $spStatus;
        }
    }

    if (!isset($associations[$assocId])) {
        $sp['core:Logout-IFrame:State'] = 'completed';
    }
}


// terminate the associations
foreach ($SPs as $assocId => $sp) {
    if ($sp['core:Logout-IFrame:State'] === 'completed') {
        $idp->terminateAssociation($assocId);
    } else {
        $logger->warning('Unable to terminate association with ' . var_export($assocId, true) . '.');
        if (isset($sp['saml:entityID'])) {
            $spId = $sp['saml:entityID'];
        } else {
            $spId = $assocId;
        }
        $logger->stats('slo-iframe-fail ' . $spId);
        Stats::log('core:idp:logout-iframe:spfail', ['sp' => $spId]);
        $state['core:Failed'] = true;
    }
}

// we are done
$idp->finishLogout($state);
