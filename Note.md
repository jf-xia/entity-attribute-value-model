php artisan migrate:generate attribute_groups,attribute_options,attribute_sets,attributes,entities,entity_attributes,entity_relations,entity_relation_ids


php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"

php artisan db:seed --class=\Encore\Admin\Auth\Database\AdminTablesSeeder

php artisan migrate