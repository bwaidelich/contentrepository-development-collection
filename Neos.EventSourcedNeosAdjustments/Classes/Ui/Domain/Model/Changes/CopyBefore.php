<?php
declare(strict_types=1);
namespace Neos\EventSourcedNeosAdjustments\Ui\Domain\Model\Changes;

/*
 * This file is part of the Neos.Neos.Ui package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\NodeAggregate\NodeName;
use Neos\EventSourcedNeosAdjustments\Ui\Fusion\Helper\NodeInfoHelper;
use Neos\Flow\Annotations as Flow;
use Neos\EventSourcedContentRepository\Domain\Context\NodeDuplication\Command\CopyNodesRecursively;
use Neos\EventSourcedContentRepository\Domain\Context\NodeDuplication\NodeDuplicationCommandHandler;
use Neos\EventSourcedContentRepository\Domain\ValueObject\UserIdentifier;

class CopyBefore extends AbstractStructuralChange
{

    /**
     * @Flow\Inject
     * @var NodeDuplicationCommandHandler
     */
    protected $nodeDuplicationCommandHandler;

    /**
     * "Subject" is the to-be-copied node; the "sibling" node is the node after which the "Subject" should be copied.
     *
     * @return boolean
     */
    public function canApply()
    {
        $nodeType = $this->getSubject()->getNodeType();

        return NodeInfoHelper::isNodeTypeAllowedAsChildNode($this->getSiblingNode()->findParentNode(), $nodeType);
    }

    public function getMode()
    {
        return 'before';
    }

    /**
     * Applies this change
     *
     * @return void
     */
    public function apply()
    {
        if ($this->canApply()) {
            $subject = $this->getSubject();

            $succeedingSibling = $this->getSiblingNode();
            $parentNodeOfSucceedingSibling = $succeedingSibling->findParentNode();

            $command = CopyNodesRecursively::create(
                $subject,
                $subject->getDimensionSpacePoint(),
                UserIdentifier::forSystemUser(), // TODO
                $parentNodeOfSucceedingSibling->getNodeAggregateIdentifier(),
                $succeedingSibling->getNodeAggregateIdentifier(),
                NodeName::fromString(uniqid('node-'))
            );

            $this->contentCacheFlusher->registerNodeChange($subject);

            $this->nodeDuplicationCommandHandler->handleCopyNodesRecursively($command)->blockUntilProjectionsAreUpToDate();

            $newlyCreatedNode = $parentNodeOfSucceedingSibling->findNamedChildNode($command->getTargetNodeName());
            $this->finish($newlyCreatedNode);
            // NOTE: we need to run "finish" before "addNodeCreatedFeedback" to ensure the new node already exists when the last feedback is processed
            $this->addNodeCreatedFeedback($newlyCreatedNode);
        }
    }
}
