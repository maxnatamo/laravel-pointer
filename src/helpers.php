<?php

if (! function_exists('getModelSubclass')) {
    /**
     *
     *
     * @param string $value
     * @param string $class
     *
     * @return string
     */
    function getModelSubclass(string $name, string $class): string
    {
        if (is_subclass_of($name, $class, allow_string: true)) {
            return $name;
        }

        return $class;
    }
}

if (! function_exists('getObjectIdentifier')) {
    /**
     * Get an identifiable name of the given object.
     *
     * @param mixed $obj
     *
     * @return string
     */
    function getObjectIdentifier(mixed $obj): string
    {
        if (isset($obj->name)) {
            return $obj->name;
        }

        return get_debug_type($obj);
    }
}
