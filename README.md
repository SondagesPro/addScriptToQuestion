# addScriptToQuestion : Allow to add easily script to question. #

Add for your admin user a question setting to put javascript snipet added when loading this question.

This add a new advanced setting in all question where user can just put javascript. Usage of {QID} and {SGQ} is allowed.

- **Compatibility** : Need [LS-SondagesPro](https://github.com/SondagesPro/LimeSurvey-SondagesPro) release 1.1.0 and up or with [LimeSurvey](https://www.limesurvey.org/) 2.50_plus_160731 and up.
  - With LimeSurvey 2.63 version : you must use 1.0.2
- **filterxsshtml** : [filterxsshtml](https://manual.limesurvey.org/Optional_settings#Security) if it's activated, admin user see it like a readonly attribute (since 2.1.0)

## Installation

### Via GIT
- Go to your LimeSurvey Directory
- Clone in plugins/addScriptToQuestion directory : `git clone https://gitlab.com/SondagesPro/addScriptToQuestion.git addScriptToQuestion`

### Via ZIP dowload
- Get the file [addScriptToQuestion.zip](https://extensions.sondages.pro/IMG/auto/addScriptToQuestion.zip)
- Extract : `unzip addScriptToQuestion.zip`
- Move the directory to plugins/ directory inside LimeSurvey

## Contribute

Contribution are welcome, for patch and issue : use [gitlab](https://gitlab.com/SondagesPro/addScriptToQuestion) still the preferred solution.

Translation can be done at [translate.sondages.pro](https://translate.sondages.pro/projects/addscripttoquestion/).
If there are issue with english string : it's a PHP issue, not a language issue.

## Home page & Copyright
- HomePage <http://extensions.sondages.pro/>
- Copyright © 2016 Denis Chenu <http://sondages.pro>
- Licence : GNU Affero General Public License <https://www.gnu.org/licenses/agpl-3.0.html>

## Changelog
- 2018-03-09 [2.2.0] Translation
- 2018-03-09 [2.1.0] Usage of XSS security
- 2018-03-08 [2.0.0] LimeSurvey 3.X version (tested on 3.4.4)
- 2017-06-27 [1.0.2] Fix {SGQ} replacement
- 2017-02-20 [1.0.0] Some fix, and compatibility
- 2016-11-17 [0.1.0] Release
