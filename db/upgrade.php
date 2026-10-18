<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Función encargada de actualizar la base de datos del módulo aiqbank
 *
 * @param int $oldversion La versión desde la que se está actualizando
 * @return bool
 */
function xmldb_aiqbank_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Aquí irán los bloques condicionales en el futuro cuando modifiques install.xml.
    // Por ahora, al ser la primera versión, solo necesitamos que la función exista
    // y retorne "true".
    
    // Ejemplo para el futuro:
    // if ($oldversion < 2023101800) {
    //     // Modificaciones a la base de datos usando $dbman
    //     upgrade_mod_savepoint(true, 2023101800, 'aiqbank');
    // }

    return true;
}
