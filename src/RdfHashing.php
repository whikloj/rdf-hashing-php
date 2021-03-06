<?php
namespace RdfHash;

use EasyRdf\Graph;
use EasyRdf\Literal;
use EasyRdf\Resource;

/**
 * Class RdfHashing
 *
 * @package RdfHash
 * @author whikloj
 * @since 2018-05-03
 */
class RdfHashing
{

    /**
     * Subject block prefix.
     *
     * @var string
     */
    private static $SUBJECT_START = "{";

    /**
     * Subject block suffix.
     *
     * @var string
     */
    private static $SUBJECT_END = "}";

    /**
     * Property block prefix.
     *
     * @var string
     */
    private static $PROPERTY_START = "(";

    /**
     * Property block suffix.
     *
     * @var string
     */
    private static $PROPERTY_END = ")";

    /**
     * Object block prefix.
     *
     * @var string
     */
    private static $OBJECT_START = "[";

    /**
     * Object block suffix.
     *
     * @var string
     */
    private static $OBJECT_END = "]";

    /**
     * Blank node constant.
     *
     * @var string
     */
    private static $BLANK_NODE = "*";

    /**
     * Calculate the SHA256 Hash of a graph.
     *
     * @param Graph $graph
     *   The graph.
     *
     * @return string
     *   The sha256 hash value.
     */
    public static function calculate(Graph $graph)
    {
        return hash("sha256", self::getGraphString($graph));
    }

    /**
     * Calculate the string definition of the graph.
     *
     * @param Graph $graph
     *   The graph.
     *
     * @return string
     *   The algorithm string.
     */
    public static function getGraphString(Graph $graph)
    {
        $subjectStrings = [];
        foreach ($graph->resources() as $resource) {
            // If no properties (its an object resource).
            if (count($resource->propertyUris()) > 0) {
                $visitedNodes = [];
                $encoded = self::encodeSubject($resource, $visitedNodes, $graph);
                $subjectStrings[] = $encoded;
            }
        }
        $subjectStrings = array_unique($subjectStrings);
        usort($subjectStrings, 'self::sortUnicode');
        $result = "";
        foreach ($subjectStrings as $s) {
            $result .= RdfHashing::$SUBJECT_START . $s . RdfHashing::$SUBJECT_END;
        }
        if (mb_detect_encoding($result, 'UTF-8') === false) {
            $result = mb_convert_encoding($result, 'UTF-8');
        }
        return $result;
    }

    /**
     * Encode a subject from the graph to a string.
     *
     * @param Resource $resource
     *   The subject resource.
     * @param array $visitedNodes
     *   Array of visited blank nodes.
     * @param Graph $graph
     *   The original graph.
     *
     * @return string
     *   The subject encoded as a string.
     */
    private static function encodeSubject(Resource $resource, array &$visitedNodes, Graph $graph)
    {
        if ($resource->isBNode()) {
            if (in_array($resource->getBNodeId(), $visitedNodes)) {
                return "";
            } else {
                $visitedNodes[] = $resource->getBNodeId();
                $result = RdfHashing::$BLANK_NODE;
            }
        } else {
            $result = $resource->getUri();
        }
        $result .= self::encodeProperties($resource, $visitedNodes, $graph);
        return $result;
    }

    /**
     * Encode the properties of a resource to a string.
     *
     * @param Resource $resource
     *   The subject resource.
     * @param array $visitedNodes
     *   Array of visited blank nodes.
     * @param Graph $graph
     *   The original graph
     *
     * @return string
     *   The properties encoded as a string.
     */
    private static function encodeProperties(Resource $resource, array &$visitedNodes, Graph $graph)
    {
        $all_properties = array_unique($resource->propertyUris());
        usort($all_properties, 'self::sortUnicode');
        $result = '';
        foreach ($all_properties as $property) {
            $objectStrings = [];
            $result .= RdfHashing::$PROPERTY_START . $property;
            foreach ($resource->all("<{$property}>") as $item) {
                $objectStrings[] = self::encodeObject($item, $visitedNodes, $graph);
            }
            $objectStrings = array_unique($objectStrings);
            usort($objectStrings, 'self::sortUnicode');
            foreach ($objectStrings as $object_string) {
                $result .= RdfHashing::$OBJECT_START . $object_string . RdfHashing::$OBJECT_END;
            }
            $result .= RdfHashing::$PROPERTY_END;
        }
        return $result;
    }

    /**
     * Encode the object of a property to a string.
     *
     * @param Resource|Literal $object
     *   The object to encode.
     * @param array $visitedNodes
     *   Array of visited blank nodes.
     * @param Graph $graph
     *   The original graph.
     *
     * @return string
     *   The object encoded as a string.
     */
    private static function encodeObject($object, array &$visitedNodes, Graph $graph)
    {
        if ($object instanceof Literal) {
            if (!is_null($object->getLang())) {
                return "\"{$object->getValue()}\"@{$object->getLang()}";
            } else {
                return "\"{$object->getValue()}\"";
            }
        } elseif ($object instanceof Resource) {
            if ($object->isBNode()) {
                return self::encodeSubject($object, $visitedNodes, $graph);
            } else {
                return $object->getUri();
            }
        }
    }

    /**
     * Static comparison using UTF-8 encoded values.
     *
     * @param string $a
     *   First comparison value.
     * @param string $b
     *   Second comparison value.
     *
     * @return int
     *   -1 if $a < $b, 1 else
     */
    private static function sortUnicode($a, $b)
    {
        $tmpA = (mb_detect_encoding($a, 'UTF-8') !== false) ? $a : mb_convert_encoding($a, 'UTF-8');
        $tmpB = (mb_detect_encoding($b, 'UTF-8') !== false) ? $b : mb_convert_encoding($b, 'UTF-8');
        return strcmp($tmpA, $tmpB);
    }
}
