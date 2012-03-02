<?php
/**
 * File containing the Content Handler interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    // @todo We must verify whether we want to type cast on the "Criterion" interface or abstract class
    eZ\Publish\API\Repository\Values\Content\Query\Criterion as AbstractCriterion,
    eZ\Publish\SPI\Persistence\Content\RestrictedVersion,
    eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * The Content Handler interface defines content operations on the storage engine.
 *
 * The basic operations which are performed on content objects are collected in
 * this interface. Typically this interface would be used by a service managing
 * business logic for content objects.
 */
interface Handler
{
    /**
     * Creates a new Content entity in the storage engine.
     *
     * The values contained inside the $content will form the basis of stored
     * entity.
     *
     * Will contain always a complete list of fields.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\CreateStruct $content Content creation struct.
     * @return \eZ\Publish\SPI\Persistence\Content Content value object
     */
    public function create( CreateStruct $content );

    /**
     * Creates a new draft version from $contentId in $srcVersion number.
     *
     * Copies all fields from $contentId in $srcVersion and creates a new
     * version of the referred Content from it.
     *
     * @param mixed $contentId
     * @param int $srcVersion
     * @return \eZ\Publish\SPI\Persistence\Content\Version
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException Thrown if $contentId and/or $srcVersion are invalid
     */
    public function createDraftFromVersion( $contentId, $srcVersion );

    /**
     * Returns the raw data of a content object identified by $id, in a struct.
     *
     * A version to load must be specified. If you want to load the current
     * version of a content object use SearchHandler::findSingle() with the
     * ContentId criterion.
     *
     * Optionally a translation filter may be specified. If specified only the
     * translations with the listed language codes will be retrieved. If not,
     * all translations will be retrieved.
     *
     * @param int|string $id
     * @param int|string $version
     * @param string[] $translations
     * @return \eZ\Publish\SPI\Persistence\Content Content value object
     */
    public function load( $id, $version, $translations = null );

    /**
     * Returns the metadata object for a content identified by $contentId.
     *
     * @param int|string $contentId
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo
     */
    public function loadContentInfo( $contentId );

    /**
     * Sets the state of object identified by $contentId and $version to $status.
     *
     * The $status can be one of STATUS_DRAFT, STATUS_PUBLISHED, STATUS_ARCHIVED
     * @todo Is this supposed to be constants from Content or Version? They differ..
     *
     * @param mixed $contentId
     * @param int $status
     * @param int $version
     * @see ezp\Content
     * @return boolean
     */
    public function setStatus( $contentId, $status, $version );

    /**
     * Sets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @param mixed $state
     * @return boolean
     * @see ezp\Content
     */
    public function setObjectState( $contentId, $stateGroup, $state );

    /**
     * Gets the object-state of object identified by $contentId and $stateGroup to $state.
     *
     * The $state is the id of the state within one group.
     *
     * @param mixed $contentId
     * @param mixed $stateGroup
     * @return mixed
     * @see ezp\Content
     */
    public function getObjectState( $contentId, $stateGroup );

    /**
     * Updates a content object meta data, identified by $contentId
     *
     * @param int $contentId
     * @param \eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct $content
     * @return \eZ\Publish\SPI\Persistence\ContentInfo
     */
    public function updateMetadata( $contentId, MetadataUpdateStruct $content );

    /**
     * Updates a content version, identified by $contentId and $versionNo
     *
     * @param int $contentId
     * @param int $versionNo
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct $content
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function updateContent( $contentId, $versionNo, UpdateStruct $content );

    /**
     * Deletes all versions and fields, all locations (subtree), and all relations.
     *
     * Removes the relations, but not the related objects. All subtrees of the
     * assigned nodes of this content objects are removed (recursively).
     *
     * @param int $contentId
     * @return boolean
     */
    public function delete( $contentId );

    /**
     * Return the versions for $contentId
     *
     * @param int $contentId
     * @return \eZ\Publish\SPI\Persistence\Content\RestrictedVersion[]
     */
    public function listVersions( $contentId );

    /**
     * Copy Content with Fields and Versions from $contentId in $version.
     *
     * Copies all fields from $contentId in $version (or all versions if false)
     * to a new object which is returned. Version numbers are maintained.
     *
     * @param mixed $contentId
     * @param int|false $version Copy all versions if left false
     * @return \eZ\Publish\SPI\Persistence\Content
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If content or version is not found
     */
    public function copy( $contentId, $version );

    /**
     * Creates a relation between $sourceContentId in $sourceContentVersionNo
     * and $destinationContentId with a specific $type.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param  \eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct $relation
     * @return \eZ\Publish\SPI\Persistence\Content\Relation
     */
    public function addRelation( RelationCreateStruct $relation );

    /**
     * Removes a relation by relation Id.
     *
     * @todo Should the existence verifications happen here or is this supposed to be handled at a higher level?
     *
     * @param mixed $relationId
     */
    public function removeRelation( $relationId );

    /**
     * Loads relations from $sourceContentId. Optionally, loads only those with $type and $sourceContentVersionNo.
     *
     * @param mixed $sourceContentId Source Content ID
     * @param mixed|null $sourceContentVersionNo Source Content Version, null if not specified
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \eZ\Publish\SPI\Persistence\Content\Relation[]
     */
    public function loadRelations( $sourceContentId, $sourceContentVersionNo = null, $type = null );

    /**
     * Loads relations from $contentId. Optionally, loads only those with $type.
     *
     * Only loads relations against published versions.
     *
     * @param mixed $destinationContentId Destination Content ID
     * @param int|null $type {@see \ezp\Content\Relation::COMMON, \ezp\Content\Relation::EMBED, \ezp\Content\Relation::LINK, \ezp\Content\Relation::ATTRIBUTE}
     * @return \eZ\Publish\SPI\Persistence\Content\Relation[]
     */
    public function loadReverseRelations( $destinationContentId, $type = null );

    /**
     * Performs the publishing operations required to set the version identified by $updateStruct->versionNo and
     * $updateStruct->id as the published one.
     *
     * The UpdateStruct will also contain an array of Content name indexed by Locale.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UpdateStruct An UpdateStruct with id, versionNo and name array
     *
     * @return \eZ\Publish\SPI\Persistence\Content The published Content
     */
    public function publish( UpdateStruct $updateStruct );
}
