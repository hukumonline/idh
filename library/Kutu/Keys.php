<?php

/**
 * anoa
 * (c) 2009-2010 madaniyah.com
 *
 * $Id: Keys.php $
 */

 /**
 * A collection of constants defining keys for registry- and session-entries.
 *
 * @author Nihki Prihadi <nihki@madaniyah.com>
 */
 
interface Kutu_Keys {

// -------- registry
    const REGISTRY_AUTH_OBJECT = 'com.kutu.registry.authObject';

// -------- app config in registry
    const REGISTRY_CONFIG_OBJECT = 'com.kutu.registry.config';

// -------- session auth namespace
    const SESSION_AUTH_NAMESPACE = 'com.kutu.session.authNamespace';

// -------- session reception controller
    const SESSION_CONTROLLER_RECEPTION = 'com.kutu.session.receptionController';
}


?>