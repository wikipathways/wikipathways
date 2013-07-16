<?php
$messages['en'] = array(
	"curationtags" => "Curation tags",
	"curationtags-no-tags" => "No default tags specified!  Please set [[CurationTagsDefinition]] with one default.",
	"curationtags-multiple-tags" => "Multiple default tags specified!  Please set [[CurationTagsDefinition]] with one default.",
	"curationtags-definition.xml" => <<<EOF
<?xml version="1.0"?>
<!--
Tag definitions must be defined below in order to be available in the curation tag GUI
To add a tag, add a <Tag> element in the xml code below. The element may contain the following attributes:
* name: The name of the tag to be used, must be unique and will be used as name for the template that will be called
* displayName: The name that will be used to display the tag in the selection menu
* description: A description about the meaning and usage of the tag
* useRevision: Let this tag apply on a single revision (default=false)
* shortName: The name that will be displayed in the history table (optional and only used if useRevision is true)
* newEditHightlight: Tags younger than this will be highlighted with the highlightAction
* highlightAction: The action to use when highlighting.  Values:
  * underConstruction:  Each row that fits the critera will have the CSS class “notUnderConstruction”  added to the row.  It can be modified at [[MediaWiki:Common.css]]
  * delete: Delete button will be available.
* bureaucrat: if present and non-empty, this tag will only be shown to users who have Bureaucrat privileges on the BrowsePathways page in the drop-down
* topTag: Should be listed in the drop-down on BrowsePathways by default.  The drop-down will show these + the bureaucrat tags if the user has privileges + "All".
* dropDown: label for the BrowsePathways drop-down.  If not specified, displayName is used.
* default: This will be the default choice.  Only the first one will be used.  If more than one are given, an error is thrown.
* icon: The icon to use to indicate this tag on the BrowsePathways page.
-->
<TagDefinitions>

<Tag
  name="Curation:ProposedDeletion"
  displayName="Proposed deletion"
  description="Use this tag to notify the author that this pathway is proposed for deletion. Please explain why the pathway should be deleted in the tag text."
  newEditHighlight="2 weeks"
  highlightAction="delete"
  icon="Deletion.png"
  bureaucrat="1"
/>

<Tag
 name="Curation:NeedsReference"
 displayName="Literature reference needed"
 description="Use this tag when the pathway requires a literature reference."
 bureaucrat="1"
 icon="NeedsRef.png"
/>

<Tag
 name="Curation:MissingXRef"
 displayName="Missing gene/protein database references"
 description="Use this tag when one or more genes/proteins/metabolites miss a database reference."
 bureaucrat="1"
 icon="MissingXref.png"
/>

<Tag
 name="Curation:NoInteractions"
 displayName="Unconnected lines"
 description="Use this tag when one or more lines that represent an interaction are not properly linked."
 bureaucrat="1"
 icon="Unconnected.png"
/>

<Tag
 name="Curation:AnalysisCollection"
 displayName="Curated collection"
 dropDown="Curated pathways"
 description="Use this tag to include the pathway in the curated collection"
 useRevision="true"
 shortName="Curated"
 topTag="1"
 icon="Curated.png"
/>

<Tag
 name="Curation:FeaturedPathway"
 displayName="Featured pathway"
 dropDown="Featured pathways"
 description="Use this tag to include the pathway in the featured pathways collection"
 useRevision="true"
 shortName="Featured"
 topTag="1"
 default="1"
 icon="Featured.png"
/>

<Tag
 name="Curation:Hypothetical"
 displayName="Hypothetical pathway"
 description="Use this tag to note a hypothetical pathway."
 icon="Hypothetical.png"
/>


<Tag
 name="Curation:Tutorial"
 displayName="Tutorial pathway"
 description="Use this tag to mark the pathway you create while doing the [[Help:Tutorial|tutorial]]. A tutorial pathway will not be included in the download archives and all tutorial pathways will be periodically deleted."
  bureaucrat="1"
 icon="Tutorial.png"
/>

<Tag
 name="Curation:InappropriateContent"
 displayName="Inappropriate content"
 description="Use this tag to mark inappropriate content. Please specify in the tag text which part of the pathway is inappropriate."
 bureaucrat="1"
/>

<Tag
 name="Curation:UnderConstruction"
 displayName="Under construction"
 newEditHighlight="yes"
 highlightAction="underConstruction"
 description="Use this tag to notify other users that this pathway is still under construction."
 bureaucrat="1"
 icon="UnderConstruction.png"
/>

<Tag
 name="Curation:MissingDescription"
 displayName="Missing description"
 description="Use this tag to mark pathways with a missing or incomplete description field"
 bureaucrat="1"
 icon="MissingDescription.png"
/>

<Tag 
  name="Curation:Stub"
  displayName="Stub"
  description="This is a Pathway for an interesting / useful topic, but it's incomplete, needs to be improved, and more details need to be filled out."
  bureaucrat="1"
  icon="Stub.png"
/>

<Tag 
  name="Curation:NeedsWork"
  displayName="Needs work"
  dropDown="Pathways in progress"
  description="Use this tag to mark pathways in need of curation. Try to suggest specific steps that would improve the content."
  topTag="1"
  icon="NeedsWork.png"
/>

<Tag 
  name="Curation:NonTypicalPathway"
  displayName="Non-typical pathway"
  description="Use this tag to notify the community that the contents do not represent a typical pathway. Please explain what the pathway represents in the tag text."
  icon="NonTypical.png"
/>

<Tag
 name="Curation:CIRM_Related"
 displayName="CIRM Related"
 description="Use this tag to mark pathways that are related to the research topics of CIRM grantees. CIRM is the California Institute for Regenerative Medicine and maintains a WikiPathways portal highlighting CIRM-related pathways."
 shortName="CIRM"
/>

<Tag
 name="Curation:Wikipedia"
 displayName="Featured in Wikipedia"
 description="Use this tag to indicate pathways that have been integrated into Wikipedia articles as interactive maps"
 useRevision="true"
 shortName="Wikipedia"
/>

<Tag
 name="Curation:Reactome_Approved"
 displayName="Reactome Approved"
 useRevision="true"
 description="Revisions with this tag are included in the curated Reactome archive. Reactome is a peer-reviewed pathway database and utilizes WikiPathways as part of its community curation and distribution effort."
 shortName="Reactome"
/>

<Tag
 name="Curation:OpenAccess"
 displayName="Open Access Publication"
 useRevision="true"
 description="Use this tag to identify pathways based on figures published in Open Access journals."
 shortName="OpenAccess"
/>

<Tag
 name="Curation:WormBase_Approved"
 displayName="WormBase Approved"
 useRevision="true"
 description="Pathways with this tag will be included in the WormBase pathway archive. This tag applies to the revision you are viewing when adding or editing the tag."
 shortName="WormBase"
/>
</TagDefinitions>
EOF
	,
	"browsepathways-all-tags" => "All pathways"
);
