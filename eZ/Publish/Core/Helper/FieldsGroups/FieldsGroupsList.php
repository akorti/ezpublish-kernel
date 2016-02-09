<?php
/**
 * This file is part of the ezpublish-kernel package.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldsGroups;

/**
 * List of content fields groups.
 *
 * Used to group fields definitions, and apply this grouping when editing / viewing content.
 */
interface FieldsGroupsList
{
    /**
     * Returns the list of fields groups identifiers.
     *
     * @return array array of fields groups identifiers
     */
    public function getGroups();

    /**
     * Returns the default field group identifier.
     *
     * @return string
     */
    public function getDefaultGroup();
}
