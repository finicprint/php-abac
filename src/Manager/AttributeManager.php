<?php

namespace PhpAbac\Manager;

use PhpAbac\Model\AbstractAttribute;
use PhpAbac\Model\Attribute;
use PhpAbac\Model\EnvironmentAttribute;

class AttributeManager
{
    /** @var array **/
    private $attributes;

    /**
     * @param array $attributes
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }
    
    /**
     * @param string $attributeId
     * @return \PhpAbac\Model\AbstractAttribute
     */
    public function getAttribute($attributeId) {
        $attributeKeys = explode('.', $attributeId);
        // The first element will be the attribute ID, then the field ID
        $attributeId = array_shift($attributeKeys);
        $attributeName = implode('.', $attributeKeys);
        // The field ID is also the attribute object property
        $attributeData = $this->attributes[$attributeId];
        return
            ($attributeId === 'environment')
            ? $this->getEnvironmentAttribute($attributeData, $attributeName)
            : $this->getClassicAttribute($attributeData, $attributeName)
        ;
    }
    
    /**
     * @param array $attributeData
     * @param string $property
     * @return \PhpAbac\Model\Attribute
     */
    private function getClassicAttribute($attributeData, $property) {
        return
            (new Attribute())
            ->setName($attributeData['fields'][$property]['name'])
            ->setType($attributeData['type'])
            ->setProperty($property)
            ->setSlug($this->slugify($attributeData['fields'][$property]['name']))
        ;
    }
    
    /**
     * @param array $attributeData
     * @param string $key
     * @return \PhpAbac\Model\EnvironmentAttribute
     */
    private function getEnvironmentAttribute($attributeData, $key) {
        return
            (new EnvironmentAttribute())
            ->setName($attributeData[$key]['name'])
            ->setType('environment')
            ->setVariableName($attributeData[$key]['variable_name'])
            ->setSlug($this->slugify($attributeData[$key]['name']))
        ;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param string $attributeType
     * @param object $user
     * @param object $object
     * @return mixed
     */
    public function retrieveAttribute(AbstractAttribute $attribute, $user, $object = null)
    {
        switch($attribute->getType()) {
            case 'user':
                return $this->retrieveClassicAttribute($attribute, $user);
            case 'resource':
                return $this->retrieveClassicAttribute($attribute, $object);
            case 'environment':
                return $this->retrieveEnvironmentAttribute($attribute);
        }
    }

    /**
     * @param Attribute $attribute
     * @param object $object
     * @return mixed
     */
    private function retrieveClassicAttribute(Attribute $attribute, $object)
    {
        $propertyPath = explode('.', $attribute->getProperty());
        $propertyValue = $object;
        foreach($propertyPath as $property) {
            $getter = 'get'.ucfirst($property);
            if(!method_exists($propertyValue, $getter)) {
                throw new \InvalidArgumentException('There is no getter for the "'.$attribute->getProperty().'" attribute for object "'.get_class($propertyValue).'"');
            }
            if (($propertyValue = $propertyValue->{$getter}()) === null) {
                return null;
            }
        }
        return $propertyValue;
    }
    
    /**
     * 
     * @param \PhpAbac\Model\EnvironmentAttribute $attribute
     * @return mixed
     */
    private function retrieveEnvironmentAttribute(EnvironmentAttribute $attribute) {
        return getenv($attribute->getVariableName());
    }
    
    /*
     * @param string $name
     * @return string
     */
    public function slugify($name)
    {
        // replace non letter or digits by -
        $name = trim(preg_replace('~[^\\pL\d]+~u', '-', $name), '-');
        // transliterate
        if (function_exists('iconv')) {
            $name = iconv('utf-8', 'us-ascii//TRANSLIT', $name);
        }
        // remove unwanted characters
        $name = preg_replace('~[^-\w]+~', '', strtolower($name));
        if (empty($name)) {
            return 'n-a';
        }
        return $name;
    }
}
