<?php

/*
 * This file is part of the JsonSchema package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JsonSchema;

use JsonSchema\Constraints\SchemaConstraint;
use JsonSchema\Constraints\Constraint;

/**
 * A JsonSchema Constraint
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 * @author Bruno Prieto Reis <bruno.p.reis@gmail.com>
 * @see    README.md
 */
class Validator extends Constraint
{
    const SCHEMA_MEDIA_TYPE = 'application/schema+json';

    /**
     * Validates the given data against the schema and returns an object containing the results
     * Both the php object and the schema are supposed to be a result of a json_decode call.
     * The validation works as defined by the schema proposal in http://json-schema.org
     *
     * {@inheritDoc}
     */
    public function check($value, $schema = null, $path = null, $i = null)
    {
        if ($this->checkMode === self::CHECK_MODE_ARR_AS_OBJ) {
            $value = $this->convertFromAllArrays($value);
        }

        $validator = $this->getFactory()->createInstanceFor('schema');
        $validator->check($value, $schema);

        $this->addErrors(array_unique($validator->getErrors(), SORT_REGULAR));
    }
    
    /**
     * Converts the result of a json_encode($data, true) to a json_encode($data, false)
     *
     * @param mixed $data
     * @throws InvalidArgumentException
     * @return mixed
     */
    private function convertFromAllArrays($data)
    {
        if (
            is_null($data) ||
            is_bool($data) ||
            is_int($data) ||
            is_float($data) ||
            is_string($data)
        ) {
            return $data;
        }

        if (is_object($data) || is_resource($data)) {
            throw new InvalidArgumentException('Found an object or resource when CHECK_MODE_ARR_AS_OBJ was set.');
        }

        // only array is left...

        foreach ($data as &$v) {
            $v = $this->convertFromAllArrays($v);
        }

        if (count(array_filter(array_keys($data), 'is_string')) == 0) {
            return $data;
        }

        return (object) $data;
    }
}
