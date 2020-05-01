<?php

namespace Baka\Blameable;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\DI;

trait BlameableTrait
{
    /**
     * Column descriptions for audit module.
     *
     * @return array
     */
    public static function getAuditColumns(): array
    {
        /* return [
            'telefono_1' => _('Teléfono Oficina 1'),
            'telefono_2' => _('Teléfono Oficina 2'),
            'celular_1' => _('Celular 1'),
            'celular_2' => _('Celular 2'),
            'email_oficina' => _('Email Oficina'),
            'direccion_contacto' => _('Dirección de Contacto'),
            'ext_1' => _('Ext 1'),
            'ext_2' => _('Ext 2'),
            'countries_id' => [
                'title' => 'name',
                'label' => _('País'),
            ],
            'cities_id' => [
                'title' => 'name',
                'label' => _('Ciudad'),
            ],
            'contact_countries_id' => [
                'title' => 'name',
                'label' => _('País del Contacto'),
            ],
            'contact_cities_id' => [
                'title' => 'name',
                'label' => _('Ciudad del Contacto'),
            ],
            'name' => _('Nombre'),
            'is_active' => [
                'name' => _('Estatus'),
                'values' => [
                    0 => _('Inactivo'),
                    1 => _('Activo'),
                ],
            ],
            'is_deleted' => [
                'name' => _('Eliminado'),
                'values' => [
                    0 => _('No'),
                    1 => _('Sí'),
                ],
            ],
        ]; */

        return [];
    }
}
