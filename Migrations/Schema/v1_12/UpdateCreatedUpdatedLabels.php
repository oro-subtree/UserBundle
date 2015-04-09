<?php

namespace Oro\Bundle\UserBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigIndexFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateCreatedUpdatedLabels implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $fields = [
            [
                'entityName' => 'Oro\Bundle\UserBundle\Entity\User',
                'field' => 'createdAt',
                'value' => 'oro.ui.created_at'
            ],
            [
                'entityName' => 'Oro\Bundle\UserBundle\Entity\User',
                'field' => 'updatedAt',
                'value' => 'oro.ui.updated_at'
            ],
            [
                'entityName' => 'Oro\Bundle\UserBundle\Entity\User',
                'field' => 'image',
                'value' => 'oro.user.avatar.label'
            ],
            [
                'entityName' => 'Oro\Bundle\UserBundle\Entity\User',
                'field' => 'serialized_data',
                'value' => 'oro.entity_serialized_fields.data.label'
            ]
        ];

        foreach ($fields as $field) {
            $queries->addQuery(
                new UpdateEntityConfigFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value']
                )
            );
            $queries->addQuery(
                new UpdateEntityConfigIndexFieldValueQuery(
                    $field['entityName'],
                    $field['field'],
                    'entity',
                    'label',
                    $field['value']
                )
            );
        }
    }
}
