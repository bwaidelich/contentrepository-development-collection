<?php
namespace Neos\EventSourcedNeosAdjustments\Eel\FlowQueryOperations;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\Projection\Content\TraversableNodeInterface;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Eel\FlowQuery\FlowQueryException;
use Neos\Eel\FlowQuery\Operations\AbstractOperation;
use Neos\EventSourcedContentRepository\Domain\ValueObject\PropertyName;
use Neos\Utility\ObjectAccess;

/**
 * Used to access properties of a ContentRepository Node. If the property mame is
 * prefixed with _, internal node properties like start time, end time,
 * hidden are accessed.
 */
class PropertyOperation extends AbstractOperation
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected static $shortName = 'property';

    /**
     * {@inheritdoc}
     *
     * @var integer
     */
    protected static $priority = 110;

    /**
     * {@inheritdoc}
     *
     * @var boolean
     */
    protected static $final = true;

    /**
     * {@inheritdoc}
     *
     * We can only handle ContentRepository Nodes.
     *
     * @param mixed $context
     * @return boolean
     */
    public function canEvaluate($context)
    {
        return (isset($context[0]) && ($context[0] instanceof TraversableNodeInterface));
    }

    /**
     * {@inheritdoc}
     *
     * @param FlowQuery $flowQuery the FlowQuery object
     * @param array $arguments the arguments for this operation
     * @return mixed
     * @throws FlowQueryException
     */
    public function evaluate(FlowQuery $flowQuery, array $arguments)
    {
        if (!isset($arguments[0]) || empty($arguments[0])) {
            throw new FlowQueryException('property() does not support returning all attributes yet', 1332492263);
        } else {
            $context = $flowQuery->getContext();
            $propertyPath = $arguments[0];

            if (!isset($context[0])) {
                return null;
            }

            /* @var $element TraversableNodeInterface */
            $element = $context[0];
            if ($propertyPath === '_path') {
                return (string)$element->findNodePath();
            } elseif ($propertyPath[0] === '_') {
                return ObjectAccess::getPropertyPath($element, substr($propertyPath, 1));
            } else {
                if ($element->getNodeType()->getPropertyType($propertyPath) === 'reference') {
                    $references = $element->findNamedReferencedNodes(PropertyName::fromString($propertyPath));
                    if ($references->isEmpty()) {
                        return null;
                    } else {
                        $arr = $references->toArray();
                        $res = reset($arr);
                        return $res;
                    }
                } elseif ($element->getNodeType()->getPropertyType($propertyPath) === 'references') {
                    return $element->findNamedReferencedNodes(PropertyName::fromString($propertyPath))->toArray();
                } else {
                    return $element->getProperty($propertyPath);
                }
            }
        }
    }
}
