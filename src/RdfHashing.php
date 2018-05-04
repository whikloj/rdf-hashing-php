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
    public function calculate(Graph $graph)
    {
        $subjectStrings = [];
        foreach ($graph->resources() as $resource) {
            // If no properties (its an object resource).
            if (count($resource->properties()) > 0) {
                $visitedNodes = [];
                $encoded = $this->encodeSubject($resource, $visitedNodes, $graph);
                $subjectStrings[] = $encoded;
            }
        }
        $subjectStrings = array_unique($subjectStrings);
        asort($subjectStrings);
        $result = "";
        foreach ($subjectStrings as $s) {
            $result .= RdfHashing::$SUBJECT_START . $s . RdfHashing::$SUBJECT_END;
        }
        return hash("sha256", $result);
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
    private function encodeSubject(Resource $resource, array &$visitedNodes, Graph $graph)
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
        $result .= $this->encodeProperties($resource, $visitedNodes, $graph);
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
    private function encodeProperties(Resource $resource, array &$visitedNodes, Graph $graph)
    {
        $all_properties = array_unique($resource->properties());
        asort($all_properties);
        $result = '';
        foreach ($all_properties as $property) {
            $objectStrings = [];
            $result .= RdfHashing::$PROPERTY_START . $property;
            foreach ($resource->all($property) as $item) {
                $objectStrings[] = $this->encodeObject($item, $visitedNodes, $graph);
            }
            $objectStrings = array_unique($objectStrings);
            asort($objectStrings);
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
    private function encodeObject($object, array &$visitedNodes, Graph $graph)
    {
        if ($object instanceof Literal) {
            return $object->getValue();
        } elseif ($object instanceof Resource) {
            if ($object->isBNode()) {
                return $this->encodeSubject($object, $visitedNodes, $graph);
            } else {
                return $object->getUri();
            }
        }
    }
}
