<?php

namespace WordPress\JsonMapper\Property;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use WordPress\JsonMapper\Import;
use WordPress\JsonMapper\ObjectWrapper;
use WordPress\JsonMapper\UseNodeVisitor;

class NamespaceResolver implements PropertyMapperInterface {

	private $scalar_types = array( 'string', 'bool', 'boolean', 'int', 'integer', 'double', 'float' );

	public function __construct() {}

	public function map_properties( ObjectWrapper $object_wrapper, PropertyMap $property_map ) {
		foreach ( $this->fetchPropertyMapForObject( $object_wrapper, $property_map ) as $property ) {
			$property_map->addProperty( $property );
		}
	}

	private function fetchPropertyMapForObject( ObjectWrapper $object, PropertyMap $originalPropertyMap ): PropertyMap {
		$intermediatePropertyMap = new PropertyMap();
		$imports                 = self::getImports( $object->getReflectedObject() );

		/** @var Property $property */
		foreach ( $originalPropertyMap as $property ) {
			$types = $property->property_types;
			foreach ( $types as $index => $type ) {
				$types[ $index ] = $this->resolveSingleType( $type, $object, $imports );
			}
			$property->property_types = $types;
			$intermediatePropertyMap->addProperty( $property );
		}

		return $intermediatePropertyMap;
	}

	/** @return Import[] */
	private static function getImports( \ReflectionClass $class ): array {
		if ( ! $class->isUserDefined() ) {
			return array();
		}

		$filename = $class->getFileName();
		if ( $filename === false || \substr( $filename, -13 ) === "eval()'d code" ) {
			throw new \RuntimeException( "Class {$class->getName()} has no filename available" );
		}

		if ( $class->getParentClass() === false ) {
			return self::getImportsForFileName( $filename );
		}

		return array_unique(
			array_merge( self::getImportsForFileName( $filename ), self::getImports( $class->getParentClass() ) ),
			SORT_REGULAR
		);
	}

	/** @return Import[] */
	private static function getImportsForFileName( string $filename ): array {
		if ( ! \is_readable( $filename ) ) {
			throw new \RuntimeException( "Unable to read {$filename}" );
		}

		$contents = \file_get_contents( $filename );
		if ( $contents === false ) {
			throw new \RuntimeException( "Unable to read {$filename}" );
		}

		$parser = ( new ParserFactory() )->create( ParserFactory::PREFER_PHP7 );

		try {
			$ast = $parser->parse( $contents );
			if ( \is_null( $ast ) ) {
				throw new \Exception( "Failed to parse {$filename}" );
			}
		} catch ( \Throwable $e ) {
			throw new \Exception( "Failed to parse {$filename}" );
		}

		$traverser = new NodeTraverser();
		$visitor   = new UseNodeVisitor();
		$traverser->addVisitor( $visitor );
		$traverser->traverse( $ast );

		return $visitor->getImports();
	}


	/** @param Import[] $imports */
	private function resolveSingleType(string $property_type, ObjectWrapper $object, array $imports ): string {
		if ( $this->is_valid_scalar_type( $property_type ) ) {
			return $property_type;
		}

		$pos = strpos( $property_type, '\\' );
		if ( $pos === false ) {
			$pos = strlen( $property_type );
		}
		$nameSpacedFirstChunk = '\\' . substr( $property_type, 0, $pos );

		$matches = \array_filter(
			$imports,
			static function ( Import $import ) use ( $nameSpacedFirstChunk ) {
				if ( $import->hasAlias() && '\\' . $import->getAlias() === $nameSpacedFirstChunk ) {
					return true;
				}

				return $nameSpacedFirstChunk === \substr( $import->getImport(), -strlen( $nameSpacedFirstChunk ) );
			}
		);

		if ( count( $matches ) > 0 ) {
			$match = \array_shift( $matches );
			if ( $match->hasAlias() ) {
				$strippedType       = \substr( $property_type, strlen( $nameSpacedFirstChunk ) );
				$fullyQualifiedType = $match->getImport() . '\\' . $strippedType;
			} else {
				$strippedMatch      = \substr( $match->getImport(), 0, -strlen( $nameSpacedFirstChunk ) );
				$fullyQualifiedType = $strippedMatch . '\\' . $property_type;
			}

			return rtrim( $fullyQualifiedType, '\\' );
		}

		$reflectedObject = $object->getReflectedObject();
		while ( true ) {
			if ( class_exists( $reflectedObject->getNamespaceName() . '\\' . $property_type ) ) {
				return $reflectedObject->getNamespaceName() . '\\' . $property_type;
			}

			$reflectedObject = $reflectedObject->getParentClass();
			if ( ! $reflectedObject ) {
				break;
			}
		}

		return $property_type;
	}

	/**
	 * @param string $property_type
	 * @return bool
	 */
	private function is_valid_scalar_type( string $property_type ): bool {
		return in_array( $property_type, $this->scalar_types, true );
	}
}
