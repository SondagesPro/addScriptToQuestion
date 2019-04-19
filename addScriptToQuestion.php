<?php
/**
 * Allow to add script to question.
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2016-2018 Denis Chenu <http://www.sondages.pro>
 * @license AGPL v3
 * @version 2.4.2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
class addScriptToQuestion extends PluginBase
{

  static protected $name = 'addScriptToQuestion';
  static protected $description = 'Allow to add easily script to question.';

  protected $storage = 'DbStorage';
  protected $settings = array(
      'scriptPositionAvailable'=>array(
          'type'=>'boolean',
          'label' => 'Show the scriptPosition attribute',
          'default' => 0,
      ),
      'scriptPositionDefault'=>array(
          'type'=>'select',
          'label' => 'Position for the script',
          'options'=>array(
            'afteranswer'=>"The script is inserted just after answer part.",
            CClientScript::POS_HEAD=>"The script is inserted in the head section right before the title element (POS_HEAD).",
            CClientScript::POS_BEGIN=>"The script is inserted at the beginning of the body section (POS_BEGIN).",
            CClientScript::POS_END=>"The script is inserted at the end of the body section (POS_END).",
            CClientScript::POS_LOAD=>"the script is inserted in the window.onload() function (POS_LOAD).",
            CClientScript::POS_READY=>"the script is inserted in the jQuery's ready function (POS_READY).",
          ),
          'default'=>CClientScript::POS_END, /* This is really the best solution */
      ),
  );

  /**
  * Add function to be used in beforeQuestionRender event and to attriubute
  */
  public function init()
  {
    $this->subscribe('beforeQuestionRender','addScript');
    $this->subscribe('newQuestionAttributes','addScriptAttribute');
  }

  /**
   * Add the script when question is rendered
   * Add QID and SGQ replacement forced (because it's before this was added by core
   */
  public function addScript()
  {
    $oEvent=$this->getEvent();
    $aAttributes=QuestionAttribute::model()->getQuestionAttributes($oEvent->get('qid'));
    if(isset($aAttributes['javascript']) && trim($aAttributes['javascript']) && $aAttributes['scriptActivate'] == 1){
      $aReplacement=array(
        'QID'=>$oEvent->get('qid'),
        'GID'=>$oEvent->get('gid'),
        'SGQ'=>$oEvent->get('surveyId')."X".$oEvent->get('gid')."X".$oEvent->get('qid'),
      );
      if(floatval(Yii::app()->getConfig('versionnumber')) >=3) {
        $script=LimeExpressionManager::ProcessString($aAttributes['javascript'], $oEvent->get('qid'), $aReplacement, 2, 0, false, false, true);
      } else {
        $script=LimeExpressionManager::ProcessString($aAttributes['javascript'], $oEvent->get('qid'), $aReplacement, false, 2, 0, false, false, true);
      }
      if($this->get('scriptPositionAvailable')) {
        $scriptPosition = isset($aAttributes['scriptPosition']) ? $aAttributes['scriptPosition'] : $this->get('scriptPositionDefault',null,null,$this->settings['scriptPositionDefault']['default']);
      } else {
        $scriptPosition = $this->get('scriptPositionDefault',null,null,$this->settings['scriptPositionDefault']['default']);
      }
      if($scriptPosition == 'afteranswer') {
        $script = "\n\n<script type=\"text/javascript\">\n"
                . $script."\n"
                . "</script>";
        $oEvent->set('answers',$oEvent->get('answers').$script);
        return;
      }
      App()->getClientScript()->registerScript("scriptAttribute{$oEvent->get('qid')}",$script,$scriptPosition);
    }
  }

  /**
   * The attribute, use readonly for 3.X version
   */
  public function addScriptAttribute()
  {
    $readonly = Yii::app()->getConfig('filterxsshtml') && !Permission::model()->hasGlobalPermission('superadmin', 'read');
    $scriptAttributes = array(
      'scriptActivate' => array(
        'types'     => '15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*', /* all question types */
        'category'  => $this->_translate('Script'),
        'sortorder' => 1,
        'inputtype' => 'switch',
        'readonly'=>$readonly,
        'caption'   => $this->_translate('Activate script execution'),
        'default'   => '1',
      ),
      'javascript'=>array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*', /* Whole question type */
        'category'=>$this->_translate('Script'), /* Workaround ? Tony Partner :)))) ? */
        'sortorder'=>1, /* Own category */
        'inputtype'=>'textarea',
        'default'=>'', /* not needed (it's already the default) */
        'expression'=>1,/* As static */
        'readonly'=>$readonly,
        'help'=>$this->_translate("You don't have to add script tag, script is register by LimeSurvey. You can use expressions, this one is static (no update during run-time)."),
        'caption'=>$this->_translate('Javascript for this question'),
      ),
    );
    if($this->get('scriptPositionAvailable',null,null,$this->settings['scriptPositionAvailable']['default']) && !$readonly){
      $scriptAttributes['scriptPosition']=array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*', /* Whole question type */
        'category'=>$this->_translate('Script'),
        'sortorder'=>1,
        'inputtype'=>'singleselect',
        'options'=>array(
          'afteranswer'=>$this->_translate("The script is inserted just after answer part."),
          CClientScript::POS_HEAD=>$this->_translate("The script is inserted in the head section right before the title element (POS_HEAD)."),
          CClientScript::POS_BEGIN=>$this->_translate("The script is inserted at the beginning of the body section (POS_BEGIN)."),
          CClientScript::POS_END=>$this->_translate("The script is inserted at the end of the body section (POS_END)."),
          CClientScript::POS_LOAD=>$this->_translate("The script is inserted in the window.onload() function (POS_LOAD)."),
          CClientScript::POS_READY=>$this->_translate("The script is inserted in the jQuery's ready function (POS_READY)."),
        ),
        'default'=>$this->get('scriptPositionDefault',null,null,$this->settings['scriptPositionDefault']['default']),
        'readonly'=>$readonly,
        'help'=>sprintf($this->_translate('Set the position of the script, see <a href="%s">Yii manual</a>.'),'http://www.yiiframework.com/doc/api/1.1/CClientScript#registerScript-detail'),
        'caption'=>$this->_translate('Position for the script'),
      );
    }

    if(method_exists($this->getEvent(),'append')) {
      $this->getEvent()->append('questionAttributes', $scriptAttributes);
    } else {
      $questionAttributes=(array)$this->event->get('questionAttributes');
      $questionAttributes=array_merge($questionAttributes,$scriptAttributes);
      $this->event->set('questionAttributes',$questionAttributes);
    }
  }

  /**
  * @see parent::gT
  */
  private function _translate($sToTranslate, $sEscapeMode = 'unescaped', $sLanguage = null)
  {
    if(is_callable($this, 'gT')) {
      return $this->gT($sToTranslate,$sEscapeMode,$sLanguage);
    }
    return $sToTranslate;
  }
}
