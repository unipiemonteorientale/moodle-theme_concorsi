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
 * Language file.
 *
 * @package   theme_concorsi
 * @copyright 2023 UPO www.uniupo.it
 * @author    Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advancedsettings'] = 'Advanced settings';
$string['backgroundimage'] = 'Background image';
$string['backgroundimage_desc'] = 'The image to display as a background of the site. The background image you upload here will override the background image in your theme preset files.';
$string['brandcolor'] = 'Brand colour';
$string['brandcolor_desc'] = 'The accent colour.';
$string['bootswatch'] = 'Bootswatch';
$string['bootswatch_desc'] = 'A bootswatch is a set of Bootstrap variables and css to style Bootstrap';
$string['choosereadme'] = 'Concorsi is a modern highly-customisable theme. This theme is intended to be used directly, or as a parent theme when creating new themes utilising Bootstrap 4.';
$string['configtitle'] = 'Concorsi';
$string['generalsettings'] = 'General settings';
$string['loginbackgroundimage'] = 'Login page background image';
$string['loginbackgroundimage_desc'] = 'The image to display as a background for the login page.';
$string['nobootswatch'] = 'None';
$string['pluginname'] = 'Concorsi';
$string['presetfiles'] = 'Additional theme preset files';
$string['presetfiles_desc'] = 'Preset files can be used to dramatically alter the appearance of the theme. See <a href="https://docs.moodle.org/dev/Boost_Presets">Boost presets</a> for information on creating and sharing your own preset files, and see the <a href="https://archive.moodle.net/boost">Presets repository</a> for presets that others have shared.';
$string['preset'] = 'Theme preset';
$string['preset_desc'] = 'Pick a preset to broadly change the look of the theme.';
$string['privacy:metadata'] = 'The Concorsi theme does not store any personal data about any user.';
$string['rawscss'] = 'Raw SCSS';
$string['rawscss_desc'] = 'Use this field to provide SCSS or CSS code which will be injected at the end of the style sheet.';
$string['rawscsspre'] = 'Raw initial SCSS';
$string['rawscsspre_desc'] = 'In this field you can provide initialising SCSS code, it will be injected before everything else. Most of the time you will use this setting to define variables.';
$string['region-side-pre'] = 'Right';
$string['showfooter'] = 'Show footer';
$string['unaddableblocks'] = 'Unneeded blocks';
$string['unaddableblocks_desc'] = 'The blocks specified are not needed when using this theme and will not be listed in the \'Add a block\' menu.';
$string['privacy:metadata:preference:draweropenblock'] = 'The user\'s preference for hiding or showing the drawer with blocks.';
$string['privacy:metadata:preference:draweropenindex'] = 'The user\'s preference for hiding or showing the drawer with course index.';
$string['privacy:metadata:preference:draweropennav'] = 'The user\'s preference for hiding or showing the drawer menu navigation.';
$string['privacy:drawerindexclosed'] = 'The current preference for the index drawer is closed.';
$string['privacy:drawerindexopen'] = 'The current preference for the index drawer is open.';
$string['privacy:drawerblockclosed'] = 'The current preference for the block drawer is closed.';
$string['privacy:drawerblockopen'] = 'The current preference for the block drawer is open.';

// Concorsi additional strings.
$string['filehash'] = 'File hash: {$a}';
$string['concorsisettings'] = 'Concorsi settings';
$string['clear'] = 'Clear';
$string['coursestartdate'] = 'Set at course start date';
$string['anonymizedates'] = 'Anonymize attempt dates';
$string['anonymizedates_desc'] = 'When enabled all quiz attempts timestamp will be cleared or set to the course start date in order to anonymize as much as possible user activity';
$string['usernamehash'] = 'Add hashed usernames in attempt report pdf';
$string['usernamehash_desc'] = 'When enabled all quiz attempt reports pdf will include the hashed username. This could be useful to prevent report repudiation';
$string['allowrefinalize'] = 'Refinalize';
$string['allowrefinalize_desc'] = 'When enabled, enabled users can finalize more than once public exams. This could limit anonynity of candidates';
$string['encryptzipfiles'] = 'Encrypt zip files';
$string['encryptzipfiles_desc'] = 'When enabled, a password will be asked to encrypt zip files';
$string['suspendmode'] = 'Suspend mode';
$string['suspendmode_desc'] = 'Candidate user will be suspended on quiz closing. You can choose if suspend all course enrolled candidates or only ones that attempt quizzes';
$string['attempted'] = 'Only candidates that attempted quiz';
$string['enrolled'] = 'All course enrolled candidates';

// Deprecated since Moodle 4.0.
$string['totop'] = 'Go to top';

// Deprecated since Moodle 4.1.
$string['currentinparentheses'] = '(current)';
$string['privacy:drawernavclosed'] = 'The current preference for the navigation drawer is closed.';
$string['privacy:drawernavopen'] = 'The current preference for the navigation drawer is open.';
