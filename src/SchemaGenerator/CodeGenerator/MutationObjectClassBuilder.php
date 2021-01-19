<?php

namespace GraphQL\SchemaGenerator\CodeGenerator;

use GraphQL\SchemaGenerator\CodeGenerator\CodeFile\ClassFile;
use GraphQL\SchemaObject\MutationObject;
use GraphQL\Util\StringLiteralFormatter;

/**
 * Class MutationObjectClassBuilder
 *
 * @package GraphQL\SchemaManager\CodeGenerator
 */
class MutationObjectClassBuilder extends ObjectClassBuilder
{
    /**
     * MutationObjectClassBuilder constructor.
     *
     * @param string $writeDir
     * @param string $objectName
     * @param string $namespace
     */
    public function __construct(string $writeDir, string $objectName, string $namespace = self::DEFAULT_NAMESPACE)
    {
        $className = $objectName . 'MutationObject';

        $this->classFile = new ClassFile($writeDir, $className);
        $this->classFile->setNamespace($namespace);
        if ($namespace !== self::DEFAULT_NAMESPACE) {
            $this->classFile->addImport('GraphQL\\SchemaObject\\MutationObject');
        }
        $this->classFile->extendsClass('MutationObject');

        // Special case for handling root mutation object
        if ($objectName === MutationObject::ROOT_MUTATION_OBJECT_NAME) {
            $objectName = '';
        }
        $this->classFile->addConstant('OBJECT_NAME', $objectName);
    }

    /**
     * @param string $fieldName
     */
    public function addScalarField(string $fieldName, bool $isDeprecated, ?string $deprecationReason)
    {
        $upperCamelCaseProp = StringLiteralFormatter::formatUpperCamelCase($fieldName);
        $this->addSimpleSelector($fieldName, $upperCamelCaseProp, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $fieldName
     * @param string $typeName
     * @param string $argsObjectName
     */
    public function addObjectField(string $fieldName, string $typeName, string $argsObjectName, bool $isDeprecated, ?string $deprecationReason)
    {
        $upperCamelCaseProp = StringLiteralFormatter::formatUpperCamelCase($fieldName);
        $this->addObjectSelector($fieldName, $upperCamelCaseProp, $typeName, $argsObjectName, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $propertyName
     * @param string $upperCamelName
     * @param bool $isDeprecated
     * @param string|null $deprecationReason
     */
    protected function addSimpleSelector(string $propertyName, string $upperCamelName, bool $isDeprecated, ?string $deprecationReason)
    {
        $method = "public function select$upperCamelName()
{
    \$this->selectField(\"$propertyName\");

    return \$this;
}";
        $this->classFile->addMethod($method, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $fieldName
     * @param string $upperCamelName
     * @param string $fieldTypeName
     * @param string $argsObjectName
     */
    protected function addObjectSelector(string $fieldName, string $upperCamelName, string $fieldTypeName, string $argsObjectName, bool $isDeprecated, ?string $deprecationReason)
    {
        $objectClassName  = $fieldTypeName . 'MutationObject';
        $method = "public function select$upperCamelName($argsObjectName \$argsObject = null)
{
    \$object = new $objectClassName(\"$fieldName\");
    if (\$argsObject !== null) {
        \$object->appendArguments(\$argsObject->toArray());
    }
    \$this->selectField(\$object);

    return \$object;
}";
        $this->classFile->addMethod($method, $isDeprecated, $deprecationReason);
    }

    /**
     * This method builds the class and writes it to the file system
     */
    public function build(): void
    {
        $this->classFile->writeFile();
    }
}
