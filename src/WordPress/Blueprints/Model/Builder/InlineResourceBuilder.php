<?php
/**
 * @file AUTOGENERATED FILE – DO NOT CHANGE MANUALLY
 * All your changes will get overridden. See the README for more details.
 */

namespace WordPress\Blueprints\Model\Builder;

use Swaggest\JsonSchema\Constraint\Properties;
use Swaggest\JsonSchema\Schema;
use WordPress\Blueprints\Model\DataClass\InlineResource;
use Swaggest\JsonSchema\Structure\ClassStructureContract;


/**
 * Built from #/definitions/InlineResource
 */
class InlineResourceBuilder extends InlineResource implements ClassStructureContract
{
    use \Swaggest\JsonSchema\Structure\ClassStructureTrait;

    /**
     * @param Properties|static $properties
     * @param Schema $ownerSchema
     */
    public static function setUpProperties($properties, Schema $ownerSchema)
    {
        $properties->resource = Schema::string();
        $properties->resource->description = "Identifies the file resource as an inline string";
        $properties->resource->const = "inline";
        $properties->contents = Schema::string();
        $properties->contents->description = "The contents of the file";
        $ownerSchema->type = Schema::OBJECT;
        $ownerSchema->additionalProperties = false;
        $ownerSchema->required = array(
            self::names()->resource,
            self::names()->contents,
        );
        $ownerSchema->setFromRef('#/definitions/InlineResource');
    }

    /**
     * @param string $resource Identifies the file resource as an inline string
     * @return $this
     * @codeCoverageIgnoreStart
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }
    /** @codeCoverageIgnoreEnd */

    /**
     * @param string $contents The contents of the file
     * @return $this
     * @codeCoverageIgnoreStart
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
        return $this;
    }
    /** @codeCoverageIgnoreEnd */

    function toDataObject()
    {
        $dataObject = new InlineResource();
        $dataObject->resource = $this->recursiveJsonSerialize($this->resource);
        $dataObject->contents = $this->recursiveJsonSerialize($this->contents);
        return $dataObject;
    }

    /**
     * @param mixed $objectMaybe
     */
    private function recursiveJsonSerialize($objectMaybe)
    {
        if ( is_array( $objectMaybe ) ) {
        	return array_map([$this, 'recursiveJsonSerialize'], $objectMaybe);
        } elseif ( $objectMaybe instanceof \Swaggest\JsonSchema\Structure\ClassStructureContract ) {
        	return $objectMaybe->toDataObject();
        } else {
        	return $objectMaybe;
        }
    }
}