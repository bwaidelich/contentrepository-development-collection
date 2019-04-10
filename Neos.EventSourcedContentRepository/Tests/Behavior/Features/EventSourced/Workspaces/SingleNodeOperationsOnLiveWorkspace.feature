@fixtures
Feature: Single Node operations on live workspace

  As a user of the CR I want to execute operations on a node in live workspace.

  Background:
    Given I have no content dimensions
    And I have the following NodeTypes configuration:
    """
    'Neos.ContentRepository.Testing:Content':
      properties:
        text:
          type: string
    """
    And the command CreateRootWorkspace is executed with payload:
      | Key                     | Value           |
      | workspaceName           | "live"          |
      | contentStreamIdentifier | "cs-identifier" |
    And the event RootNodeAggregateWithNodeWasCreated was published with payload:
      | Key                           | Value                         |
      | contentStreamIdentifier       | "cs-identifier"               |
      | nodeAggregateIdentifier       | "lady-eleonode-rootford"      |
      | nodeTypeName                  | "Neos.ContentRepository:Root" |
      | visibleInDimensionSpacePoints | [{}]                          |
      | initiatingUserIdentifier      | "user-identifier"             |
      | nodeAggregateClassification   | "root"                        |
    And the event NodeAggregateWithNodeWasCreated was published with payload:
      | Key                           | Value                                    |
      | contentStreamIdentifier       | "cs-identifier"                          |
      | nodeAggregateIdentifier       | "nody-mc-nodeface"                       |
      | nodeTypeName                  | "Neos.ContentRepository.Testing:Content" |
      | originDimensionSpacePoint     | {}                                       |
      | visibleInDimensionSpacePoints | [{}]                                     |
      | parentNodeAggregateIdentifier | "lady-eleonode-rootford"                 |
      | nodeName                      | "child"                                  |
      | nodeAggregateClassification   | "regular"                                |
    And the graph projection is fully up to date

  Scenario: Set property of a node
    Given the command "SetNodeProperty" is executed with payload:
      | Key                       | Value                             |
      | contentStreamIdentifier   | "cs-identifier"                   |
      | nodeAggregateIdentifier   | "nody-mc-nodeface"                |
      | originDimensionSpacePoint | {}                                |
      | propertyName              | "text"                            |
      | value                     | {"value":"Hello","type":"string"} |

    Then I expect exactly 4 events to be published on stream with prefix "Neos.ContentRepository:ContentStream:cs-identifier"
    And event at index 3 is of type "Neos.EventSourcedContentRepository:NodePropertyWasSet" with payload:
      | Key                       | Expected           |
      | contentStreamIdentifier   | "cs-identifier"    |
      | nodeAggregateIdentifier   | "nody-mc-nodeface" |
      | originDimensionSpacePoint | []                 |
      | propertyName              | "text"             |
      | value.value               | "Hello"            |

    When the graph projection is fully up to date
    And I am in the active content stream of workspace "live" and Dimension Space Point {}
    Then I expect a node identified by aggregate identifier "nody-mc-nodeface" to exist in the subgraph
    And I expect this node to have the properties:
      | Key  | Value |
      | text | Hello |

  Scenario: Show a node
    Given the command "ShowNode" is executed with payload:
      | Key                          | Value              |
      | contentStreamIdentifier      | "cs-identifier"    |
      | nodeAggregateIdentifier      | "nody-mc-nodeface" |
      | affectedDimensionSpacePoints | [{}]               |

    Then I expect exactly 4 events to be published on stream with prefix "Neos.ContentRepository:ContentStream:cs-identifier"
    And event at index 3 is of type "Neos.EventSourcedContentRepository:NodeWasShown" with payload:
      | Key                          | Expected           |
      | contentStreamIdentifier      | "cs-identifier"    |
      | nodeAggregateIdentifier      | "nody-mc-nodeface" |
      | affectedDimensionSpacePoints | [[]]               |

    When the graph projection is fully up to date
    And I am in the active content stream of workspace "live" and Dimension Space Point {}

    Then I expect a node identified by aggregate identifier "nody-mc-nodeface" to exist in the subgraph