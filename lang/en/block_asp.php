<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * ASP block english language strings
 *
 * @package   block_asp
 * @copyright 2011 Lancaster University Network Services Limited
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Core strings.
$string['asp:addinstance'] = 'Add a new asp block';
$string['pluginname']                   = 'ASPs';
$string['asp']                     = 'ASP';

$string['activetasktitle']              = 'Currently active task';
$string['addaasp']                 = 'Add a asp';
$string['addanotherasp']           = 'Add another asp';
$string['addemail']                     = 'Add email template';
$string['addroletostep']                = 'Add role to step';
$string['addstep']                      = 'Add an additional step to this asp';
$string['addstepafter']                 = 'Add a step after this point';
$string['addtask']                      = 'Add task';
$string['any']                          = 'Any ';
$string['appliesto']                    = 'Applies to';
$string['atendfinishasp']          = 'finish the asp';
$string['atendgobackataspcreate']  = 'An atendgobacktostep setting cannot be specified at asp creation as no steps exist to reference';
$string['atendgobacktostep']            = 'At the end of step {$a}';
$string['atendgobacktostepinfo']        = 'After step {$a->stepcount}, go back to step number {$a->atendgobacktostep}.';
$string['atendgobacktostepno']          = 'go back to step {$a->stepno} ({$a->name})';
$string['atendstop']                    = 'After step {$a->stepcount}, this asp will end.';
$string['automaticallyfinish']          = 'Automatically finish';

$string['cannotdeleteaspinuseby']  = 'Cannot delete, this asp is used in {$a} places.';
$string['cannotremoveemailinuse']       = 'Unable to remove email template -- this template is currently in use';
$string['cannotremoveonlystep']         = 'Unable to remove step. This is the only asp in the step';
$string['cannotremovestepsinuse']       = 'Unable to remove step. This step is currently active in {$a} asps';
$string['cannotremoveaspinuse']    = 'Unable to remove asp -- this asp is currently in use';
$string['cannotstepflowinuse']          = 'Unable to remove step -- this step is currently in use';
$string['clone']                        = 'Clone';
$string['clonedname']                   = '{$a} (cloned)';
$string['clonedshortname']              = '{$a}_cloned';
$string['cloneasp']                = 'Clone asp';
$string['cloneaspinstructions']    = 'Clone asp instructions';
$string['cloneaspname']            = 'Clone asp \'{$a}\'';
$string['commentlabel']                 = 'Update asp comment';
$string['comments']                     = 'Comments';
$string['confirm']                      = 'Confirm';
$string['confirmemaildeletetitle']      = 'Delete email template \'{$a}\'?';
$string['confirmfinishstep']            = 'Are you sure that you want to mark this step ({$a}) as complete?';
$string['confirmjumptostep']            = 'Are you sure that you want to jump to step {$a->stepno} ({$a->stepname})?';
$string['confirmstepdeletetitle']       = 'Delete step \'{$a}\'?';
$string['confirmaspdeletetitle']   = 'Delete asp \'{$a}\'?';
$string['contexthasactiveasp']     = 'This context already has an active asp.';
$string['coursestudentclose']           = 'the course is closed to students';
$string['coursestudentopen']            = 'the course is opened to students';
$string['coursetutorclose']             = 'the course is closed to tutors';
$string['coursetutoropen']              = 'the course is opened to tutors';
$string['coursestartdate']              = 'the course start date';
$string['create']                       = 'Create';
$string['createemail']                  = 'Create new email template';
$string['createstep']                   = 'Create step';
$string['createstepinstructions']       = 'Some instructions on how to create a step';
$string['createstepname']               = 'Create new step for asp \'{$a}\'';
$string['createtask']                   = 'Creating new task for step {$a}';
$string['createtemplate']               = 'Create template';
$string['createasp']               = 'Create new asp';
$string['crontaskautostepfinisher']     = 'ASP step finisher';
$string['crontaskextranotify']          = 'ASP step extra notify';
$string['currentlyinuseby']             = 'This asp is currently in use by';

$string['days']                         = 'Days';
$string['dayas']                        = 'same day as';
$string['dayafter']                     = '{$a} day after';
$string['daysafter']                    = '{$a} days after';
$string['daybefore']                    = '{$a} day before';
$string['daysbefore']                   = '{$a} days before';
$string['defaultonactivescript']        = '# You may place a set of actions to complete when marking this step active here';
$string['defaultoncompletescript']      = '# You may place a set of actions to complete when marking this step finished here';
$string['defaultonextranotifyscript']   = '# You may place a set of actions to  marking this step send notification';
$string['defaultstepdescription']       = 'A description for this step should go here';
$string['defaultstepinstructions']      = 'Do x, then y, then z.';
$string['defaultstepname']              = 'First step';
$string['defaultaspdescription']   = 'A description for this asp';
$string['delete']                       = 'Delete';
$string['deleteemail']                  = 'Delete email';
$string['deleteemailcheck']             = 'Are you absolutely sure that you want to completely delete the email template \'{$a}\'?';
$string['deletestep']                   = 'Delete step';
$string['deletestepcheck']              = 'Are you absolutely sure that you want to completely delete the step \'{$a}\'?';
$string['deletetask']                   = 'Delete task';
$string['deletetaskcheck']              = 'Are you sure you wish to delete the task \'{$a->taskname}\' from step \'{$a->stepname}\'?';
$string['deletetasktitle']              = 'Delete task \'{$a->taskname}\' for step \'{$a->stepname}\' confirmation';
$string['deletetemplate']               = 'Delete template';
$string['deleteasp']               = 'Delete asp';
$string['deleteaspcheck']          = 'Are you absolutely sure that you want to completely delete the asp {$a}?';
$string['description']                  = 'Description';
$string['disabled']                     = 'Disabled';
$string['disableasp']              = 'Disable asp';
$string['doerstitle']                   = 'Roles';
$string['doertitle']                    = 'Roles responsible for this step';
$string['donotautomaticallyfinish']     = 'Do not automatically finish';
$string['donotnotify']                  = 'Do not send extra notification';

$string['edit']                         = 'Edit';
$string['editcomments']                 = 'Edit comments';
$string['editemail']                    = 'Edit email template \'{$a}\'';
$string['editingcommentfor']            = 'Editing comment for \'{$a->stepname}\' on {$a->contextname}';
$string['editstep']                     = 'Edit step';
$string['editstepinstructions']         = 'Some instructions on what this page is for and a general page introduction. Mention the scripts, but their help files should give more information on how they work.';
$string['editstepname']                 = 'Editing step \'{$a}\'';
$string['editsteps']                    = 'Edit steps for asp \'{$a}\'';
$string['edittask']                     = 'Edit task';
$string['edittemplate']                 = 'Edit template';
$string['edittemplateinstructions']     = 'Some instructions on how to create an email template';
$string['editasp']                 = 'Editing asp \'{$a}\'';
$string['editaspinstructions']     = 'Edit asp instructions';
$string['emaildescription']             = 'E-mail templates may be used by the various scripts in a asp step';
$string['emailfrom']                    = '{$a} asp system';
$string['emaillist']                    = 'Email email templates';
$string['emailmessage']                 = 'Message';
$string['emailsettings']                = 'E-mail template settings';
$string['emailsubject']                 = 'Subject';
$string['emailtemplateexists']          = 'Email template \'{$a}\' which was attempted to import already exists. Existing template is preserved.';
$string['emptyfield']                   = 'The required field is empty: {$a}';
$string['enabled']                      = 'Enabled';
$string['enabledasp']              = 'Enabled';
$string['enableasp']               = 'Enable asp';
$string['export']                       = 'Export';
$string['exportasp']               = 'Export asp';

$string['finish']                       = 'Finish';
$string['finishstep']                   = 'Finish step';
$string['finishstepautomatically']      = 'This step was automatically finished by asp system at {$a}.';
$string['finishstepfor']                = 'Finish step \'{$a->stepname}\' on {$a->contextname}';
$string['finishstepinstructions']       = 'You are about to mark this step as complete, and move to the next step in the asp. A summary of the step is shown below -- you may wish to update the comment below.';
$string['format_html']                  = 'html';
$string['format_plain']                 = 'plain';
$string['format_unknown']               = 'unknown';

$string['general']                      = 'General';

$string['hidetask']                     = 'Disable task';

$string['importfile']                   = 'File';
$string['importsuccess']                = 'Importing was sucessful. You will be redirected to asp editing page shortly.';
$string['importasp']               = 'Import asp';
$string['instructions']                 = 'Instructions';
$string['inuseby']                      = 'It is currently in use in {$a} locations.';
$string['invalidactivitysettingcolumn'] = 'The column specified ({$a}) does not exist.';
$string['invalidappliestomodule']       = 'An invalid appliesto value was specified';
$string['invalidappliestotable']        = 'The database table for {$a} was not found. It may not be possible to use this command for this type of asp';
$string['invalidbody']                  = 'An invalid body was specified';
$string['invalidcapability']            = 'Invalid capability specified.';
$string['invalidclearmustendcommand']   = 'There should be nothing after the word \'clear\'.';
$string['invalidcommand']               = 'An invalid command was specified in the script. The command used was {$a}';
$string['invalidemailemail']            = 'An invalid email email was specified. The email specified was \'{$a}\'';
$string['invalidemailshortname']        = 'Invalid shortname specified ({$a})';
$string['invalidfield']                 = 'An invalid field was specified in the data. The field was \'{$a}\'';
$string['invalidformat']                = 'Invalid format has been specified: {$a}';
$string['invalidid']                    = 'An invalid id was specified';
$string['invalidinstructions']          = 'Invalid step instructions were specified';
$string['invalidmissingvalue']          = 'Invalid command, value is missing.';
$string['invalidname']                  = 'An invalid name was specified';
$string['invalidobsoletesetting']       = 'An invalid obsolete value was specified. Valid settings are 0, or 1';
$string['invalidpermission']            = 'Invalid permission specified. The valid permissions are inherit, allow, prevent, or prohibit.';
$string['invalidrole']                  = 'An invalid role ({$a}) was specified whilst processing the script';
$string['invalidscript']                = 'The script you specified was invalid. {$a}';
$string['invalidshortname']             = 'An invalid shortname was specified';
$string['invalidstate']                 = 'Invalid state';
$string['invalidstep']                  = 'Invalid step specified.';
$string['invalidstepid']                = 'Invalid step id specified.';
$string['invalidstepno']                = 'Invalid step number specified.';
$string['invalidsubject']               = 'An invalid subject was specified';
$string['invalidsyntaxmissingto']       = 'Invalid command syntax - missing to component';
$string['invalidsyntaxmissingx']        = 'Invalid command syntax - missing \'{$a}\'.';
$string['invalidtarget']                = 'Invalid activity target';
$string['invalidtodo']                  = 'Invalid todo specified';
$string['invalidvisibilitysetting']     = 'An invalid visibility option was specified. Valid options are visible and hidden. You specified {$a}.';
$string['invalidwordnotclearorset']     = 'Expected \'clear\' or \'set\'.';
$string['invalidasp']              = 'Invalid asp specified.';
$string['invalidaspid']            = 'An invalid asp was specified';
$string['invalidaspname']          = 'An invalid asp name was specified';
$string['invalidaspstepno']        = 'The specified step number could not be found in this asp';

$string['jumpstep']                     = 'Jump to step';
$string['jumptostep']                   = 'Jump to step';
$string['jumptostepcheck']              = 'Are you sure you wish to jump from step \'{$a->fromstep}\' to step \'{$a->tostep}\' for the asp on {$a->aspon}?';
$string['jumptostepcommentaddition']    = '<p>[Note: the asp just jumped from step \'{$a->fromstep}\'. This comment may seem out-of-context.]</p>{$a->comment}';
$string['jumptostepon']                 = 'Jump to step \'{$a->stepname}\' on {$a->contextname}';
$string['jumptosteptitle']              = 'Jump to step \'{$a->tostep}\' for \'{$a->aspon}\' confirmation';

$string['lastmodified']                 = 'Last modified';

$string['managedescription']            = 'On this page you can create end edit asps and the email templates that they use.';
$string['manageemails']                 = 'Manage email templates';
$string['manageasps']              = 'Manage asps';
$string['messageprovider:notification'] = 'ASP notifications and alerts';
$string['missingfield']                 = 'The required field is missing: {$a}';
$string['movedown']                     = 'Move down';
$string['moveup']                       = 'Move up';

$string['name']                         = 'Name';
$string['nameinuse']                    = 'The name specified is already in use. Names must be unique';
$string['nameshortname']                = '{$a->name} ({$a->shortname})';
$string['noactiveasp']             = 'There is currently no active asp.';
$string['nocomment']                    = 'No comment yet...';
$string['nocomments']                   = 'No comments have been made about this step yet';
$string['nomorestepsleft']              = 'The asp has been completed.';
$string['norolesspecified']             = 'No roles were specified';
$string['nosuchrole']                   = 'Role {$a} does not exist';
$string['notacourse']                   = 'This is not a course';
$string['notallowedtodothisstep']       = 'You do not have permission to make changes to this step at present';
$string['notanactivity']                = 'The command {$a} may only be used with an activity';
$string['notaasp']                 = 'This is not a valid asp file';
$string['notcurrentlyinuse']            = 'It is currently not in use.';
$string['notificationdate']             = 'Notification date';
$string['notuniquestep']                = 'Step {$a} is not unique';
$string['notutfencoding']               = 'This file is not UTF-8 encoded';
$string['noasp']                   = 'There is currently no asp assigned for this page';
$string['noasps']                  = 'There are currently no available asps';

$string['obsoleteasp']             = 'Obsoleted';
$string['onactivescript']               = 'On step activation';
$string['oncompletescript']             = 'On step completion';
$string['onextranotifyscript']          = 'Notify while step is active';
$string['overview']                     = 'Overview';
$string['overviewtitle']                = 'Overview of {$a->aspname} asp on {$a->contexttitle}';

$string['percentcomplete']              = '{$a}% complete';

$string['quizopendate']                 = 'the quiz open date';
$string['quizclosedate']                = 'the quiz close date';

$string['remove']                       = 'Remove';
$string['removerolefromstep']           = 'Remove role from step';
$string['removestep']                   = 'Remove step';
$string['removetask']                   = 'Remove task';
$string['removeasp']               = 'Remove asp';
$string['removeaspcheck']          = 'Are you sure that you wish to remove the asp \'{$a->aspname}\' from {$a->contexttitle}? This action removes all associated data, and cannot be reversed!';
$string['removeaspfromcontext']    = 'Remove asp \'{$a->aspname}\' from {$a->contexttitle}?';
$string['roles']                        = 'Roles';

$string['shortname']                    = 'Shortname';
$string['shortnameinuse']               = 'The shortname specified is already in use. Shortnames must be unique';
$string['shortnametaken']               = 'This short name is already in use by another asp ({$a})';
$string['shortnametakenemail']          = 'This shortname is already in use by another email template ({$a})';
$string['shownamesx']                   = 'Show names ({$a})';
$string['showpeoplecandotask']          = 'People who can do this task';
$string['showtask']                     = 'Enable task';
$string['state']                        = 'State';
$string['state_aborted']                = 'Aborted';
$string['state_active']                 = 'Active';
$string['state_completed']              = 'Complete';
$string['state_history']                = 'History';
$string['state_history_aborted']        = 'Aborted';
$string['state_history_active']         = 'Activated';
$string['state_history_completed']      = 'Completed';
$string['state_history_detail']         = '{$a->newstate} by {$a->user} at {$a->time}.';
$string['state_notstarted']             = 'Not started';
$string['status']                       = 'Current status';
$string['step']                         = 'Step';
$string['stepactivation']               = 'Step activation';
$string['stepactivation_help']          = 'Step activation';
$string['stepactivation_link']          = 'block/asp';
$string['stepcompletion']               = 'Step completion';
$string['stepcompletion_help']          = 'Step completion';
$string['stepcompletion_link']          = 'block/asp';
$string['stepextranotify']              = 'Step extra notification';
$string['stepextranotify_help']         = 'Set up email notification to be sent automatically to the chosen recipients on selected notification date.';
$string['stepextranotify_link']         = 'block/asp';
$string['stepfinishconfirmation']       = 'The step was successfully finished. You have completed all of the required work at this stage';
$string['stepinstructions']             = 'Instructions';
$string['stepname']                     = 'Step name';
$string['stepno']                       = 'Step No.';
$string['stepnotexist']                 = 'Step to go at the end does not exist in the imported data: {$a}';
$string['steps']                        = 'Steps';
$string['stepsettings']                 = 'Step settings';

$string['task']                         = 'Task';
$string['taskcomplete']                 = 'Task complete';
$string['tasknotspecified']             = 'No task was specified';
$string['thisaspappliesto']        = 'This asp applies to';
$string['tobecompletedby']              = 'To be completed by';
$string['todocannotchangestepid']       = 'It is not possible to change the stepid for an existing todo task';
$string['tododone']                     = 'Marked {$a} as complete';
$string['todolisttitle']                = 'Tasks for completion';
$string['todotask']                     = 'Task';
$string['todotitle']                    = 'Items to complete for this step';
$string['todoundone']                   = 'Marked {$a} as incomplete';

$string['updatecomment']                = 'Update comment';

$string['vieweditemail']                = 'View/Edit email';
$string['vieweditasp']             = 'View/Edit asp';

$string['asp:dostep']              = 'Permission to perform a step';
$string['asp:editdefinitions']     = 'Permission to edit asp details';
$string['asp:manage']              = 'Permission to manage asps';
$string['asp:view']                = 'Permission to view asp information';
$string['aspactive']               = 'This asp is currently enabled (<a href="{$a}" title="Disable this asp">disable it</a>). ';
$string['aspalreadyset']           = 'A asp has already been set for this step. Steps cannot be reassigned to a different asp';
$string['aspalreadyassigned']      = 'A asp is already assigned to this context. Only one asp may be assigned to any one context at a time.';
$string['aspimport']               = 'ASP importing';
$string['aspinformation']          = 'ASP information';
$string['asplist']                 = 'ASPs';
$string['aspnotassigned']          = 'The \'{$a->aspname}\' asp is not assigned to the specified context';
$string['aspnotassignedtocontext'] = 'The \'{$a->aspname}\' asp is not assigned to {$a->contexttitle}';
$string['aspobsolete']             = 'This asp is currently marked as disabled (<a href="{$a}" title="Re-enable this asp">enable it</a>). ';
$string['aspoverview']             = 'ASP overview';
$string['aspsettings']             = 'ASP settings';
$string['aspstatus']               = 'ASP status';
$string['aspsteps']                = 'ASP steps';
$string['aspusage']                = 'ASP usage';

$string['xmlloadfailed']                = 'Failed loading XML with following problems:';

$string['youandanyother']               = 'You, or any other ';
$string['youor']                        = ', or ';
