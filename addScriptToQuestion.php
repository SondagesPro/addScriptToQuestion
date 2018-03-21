<?php
/**
 * Allow to add script to question.
 *
 * @author Denis Chenu <denis@sondages.pro>
 * @copyright 2016-2018 Denis Chenu <http://www.sondages.pro>
 * @license AGPL v3
 * @version 2.2.2
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
   * Add QID and SGQ replacement forced (because it's before this was added by core)
   */
  public function addScript()
  {
    $oEvent=$this->getEvent();
    $aAttributes=QuestionAttribute::model()->getQuestionAttributes($oEvent->get('qid'));
    if(isset($aAttributes['javascript']) && trim($aAttributes['javascript'])){
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
      $aAttributes['scriptPosition']=isset($aAttributes['scriptPosition']) ? $aAttributes['scriptPosition'] : CClientScript::POS_END;
      App()->getClientScript()->registerScript("scriptAttribute{$oEvent->get('qid')}",$script,$aAttributes['scriptPosition']);
    }
  }

  /**
   * The attribute, use readonly for 3.X version
   */
  public function addScriptAttribute()
  {
    $readonly = Yii::app()->getConfig('filterxsshtml') && !Permission::model()->hasGlobalPermission('superadmin', 'read');
    $scriptAttributes = array(
      'javascript'=>array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*', /* Whole question type */
        'category'=>gT('Script'), /* Workaround ? Tony Partner :)))) ? */
        'sortorder'=>1, /* Own category */
        'inputtype'=>'textarea',
        'default'=>'', /* not needed (it's already the default) */
        'expression'=>1,/* As static */
        'readonly'=>$readonly,
        'help'=>$this->gT("You don't have to add script tag, script is register by LimeSurvey. You can use expressions, this one is static (no update during run-time)."),
        'caption'=>$this->gT('Javascript for this question'),
      ),
    );
    if($this->get('scriptPositionAvailable',null,null,$this->settings['scriptPositionAvailable']['default']) && !$readonly){
      $scriptAttributes['scriptPosition']=array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*', /* Whole question type */
        'category'=>gT('Script'),
        'sortorder'=>1,
        'inputtype'=>'singleselect',
        'options'=>array(
          CClientScript::POS_HEAD=>$this->gT("The script is inserted in the head section right before the title element (POS_HEAD)."),
          CClientScript::POS_BEGIN=>$this->gT("The script is inserted at the beginning of the body section (POS_BEGIN)."),
          CClientScript::POS_END=>$this->gT("The script is inserted at the end of the body section (POS_END)."),
          CClientScript::POS_LOAD=>$this->gT("The script is inserted in the window.onload() function (POS_LOAD)."),
          CClientScript::POS_READY=>$this->gT("The script is inserted in the jQuery's ready function (POS_READY)."),
        ),
        'default'=>$this->get('scriptPositionDefault',null,null,$this->settings['scriptPositionDefault']['default']),
        'readonly'=>Yii::app()->getConfig('filterxsshtml') && !Permission::model()->hasGlobalPermission('superadmin', 'read'),
        'help'=>sprintf($this->gT('Set the position of the script, see <a href="%s">Yii manual</a>.'),'http://www.yiiframework.com/doc/api/1.1/CClientScript#registerScript-detail'),
        'caption'=>$this->gT('Position for the script'),
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
}
