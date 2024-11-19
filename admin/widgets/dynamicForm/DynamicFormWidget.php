<?php

namespace admin\widgets\dynamicForm;

use DOMDocument;
use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Yii;
use yii\base\{InvalidConfigException, Model, Widget};
use yii\bootstrap5\Html;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\View;

/**
 * Class AppDynamicFormWidget
 *
 * @package admin\widgets\dynamic_form
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read string $hashVarName
 */
class DynamicFormWidget extends Widget
{
    public const WIDGET_NAME = 'dynamicform';

    public string $widgetContainer;

    public string $widgetBody;

    public string $widgetItem;

    public string|int $limit = 999;

    public string $insertButton;

    public string $deleteButton;
    /**
     * 'bottom' or 'top';
     */
    public string $insertPosition = 'bottom';
    /**
     * The model used for the form
     */
    public ActiveRecord|Model $model;
    /**
     * Form ID
     */
    public string $formId;
    /**
     * Fields to be validated.
     */
    public array $formFields;

    public int $min = 1;
    /**
     * Значения по умолчанию
     */
    public array $defaultValues = [];
    /**
     * Обертка для внутреннего контейнера
     */
    public string $innerWidgetWrapper = '';

    private array $_options = [];

    private string|array $_insertPositions = ['bottom', 'top'];
    /**
     * The hashed global variable name storing the pluginOptions.
     */
    private ?string $_hashVar;
    /**
     * The Json encoded options.
     */
    private string $_encodedOptions = '';

    /**
     * Initializes the widget.
     *
     * @throws InvalidConfigException
     */
    final public function init(): void
    {
        parent::init();

        if (empty($this->widgetContainer) || (preg_match('/^\w+$/', $this->widgetContainer) === 0)) {
            throw new InvalidConfigException(
                'Invalid configuration to property "widgetContainer". 
                Allowed only alphanumeric characters plus underline: [A-Za-z0-9_]'
            );
        }
        if (empty($this->widgetBody)) {
            throw new InvalidConfigException("The 'widgetBody' property must be set.");
        }
        if (empty($this->widgetItem)) {
            throw new InvalidConfigException("The 'widgetItem' property must be set.");
        }
        if (empty($this->model)) {
            throw new InvalidConfigException("The 'model' property must be set.");
        }
        if (empty($this->formId)) {
            throw new InvalidConfigException("The 'formId' property must be set.");
        }
        if (empty($this->insertPosition) || !in_array($this->insertPosition, $this->_insertPositions, true)) {
            throw new InvalidConfigException(
                "Invalid configuration to property 'insertPosition' (allowed values: 'bottom' or 'top')"
            );
        }
        if (empty($this->formFields)) {
            throw new InvalidConfigException("The 'formFields' property must be set.");
        }

        $this->initOptions();
    }

    /**
     * Initializes the widget options.
     */
    private function initOptions(): void
    {
        $this->_options['widgetContainer'] = $this->widgetContainer;
        $this->_options['widgetBody'] = $this->widgetBody;
        $this->_options['widgetItem'] = $this->widgetItem;
        $this->_options['limit'] = $this->limit;
        $this->_options['insertButton'] = $this->insertButton;
        $this->_options['deleteButton'] = $this->deleteButton;
        $this->_options['insertPosition'] = $this->insertPosition;
        $this->_options['formId'] = $this->formId;
        $this->_options['min'] = $this->min;
        $this->_options['fields'] = [];

        foreach ($this->formFields as $field) {
            $this->_options['fields'][] = [
                'id' => Html::getInputId($this->model, '[{}]' . $field),
                'name' => Html::getInputName($this->model, '[{}]' . $field)
            ];
        }

        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    final public function run(): void
    {
        $content = ob_get_clean();
        $crawler = new Crawler();
        $crawler->addHTMLContent($content, Yii::$app->charset);
        $results = $crawler->filter($this->widgetItem);
        $document = new DOMDocument('1.0', Yii::$app->charset);
        $document->appendChild($document->importNode($results->first()->getNode(0), true));
        $this->_options['template'] = trim($document->saveHTML());

        if (isset($this->_options['min']) && $this->_options['min'] === 0 && $this->model->isNewRecord) {
            $content = $this->removeItems($content);
        }

        $this->hashOptions();
        $view = $this->getView();
        $widgetRegistered = $this->registerHashVarWidget();
        $this->_hashVar = $this->getHashVarName();

        if ($widgetRegistered) {
            $this->registerOptions($view);
            $this->registerAssets($view);
        }

        echo Html::tag('div', $content, ['class' => $this->widgetContainer, 'data-dynamicform' => $this->_hashVar]);

        foreach ($this->defaultValues as $field => $defaultValue) {
            $this->defaultValues[Html::getInputId($this->model, '[{}]' . $field)] = $defaultValue;
            unset($this->defaultValues[$field]);
        }
        $jsonDefaultValues = Json::htmlEncode($this->defaultValues);
        $hasInner = $this->innerWidgetWrapper ? 'true' : 'false';
        $exceptInnerInput = $this->innerWidgetWrapper ? ".not('.$this->innerWidgetWrapper input')" : null;
        $this->view->registerJs(
            <<<JS
let fun$this->widgetContainer = function(e, item) {
  $('[data-bs-toggle="tooltip"]').tooltip()
  if ($hasInner) {
    $(".$this->innerWidgetWrapper").on("afterInsert", fun$this->innerWidgetWrapper)
  }
  item = $(item)
  let formInput = item.find('.form-group input,.file-input input,input.form-control,textarea.form-control').not('input[type=hidden]')$exceptInnerInput
  let id = formInput.last().attr('id')
  let number = id ? id.match(/[-\d]+/) : false
  if (!number) {
    console.error('Unable to find new form number skipping custom login')
    return
  }
  item.find('.file-input input[type=text]').each(function () {
    $(this).attr('value', '')
  })
  item.find('.file-input img.preview-image').each(function () {
    $(this).attr('src', '')
  })
  let countSeparators = number[0].split('-').length - 1
  let i = 2
  let regex = '-\\\d+-'
  while (i < countSeparators) {
    regex += '\\\d+-'
    i++
  }
  regex = new RegExp(regex)
  item.find('.file-input button').each(function () {
    $(this).attr('onclick', $(this).attr('onclick').replace(regex, number))
  })
  const defaultValues = $jsonDefaultValues
  for (const [key, value] of Object.entries(defaultValues)) {
    let input = item.find('#' + key.replace('{}', number))
    let nodeName = input.prop("nodeName")
    switch (nodeName) {
      case 'TEXTAREA':
        input.val(value)
        input.text(value)
        break
      case 'INPUT':
        if (input.attr('type') === 'checkbox') {
            input.prop('checked', value)
        } else input.val(value)
        break
      default:
        input.val(value)
        break
    }
  }
}
$(".$this->widgetContainer").on("afterInsert", fun$this->widgetContainer);
JS
        );
    }

    /**
     * Clear HTML widgetBody. Required to work with zero or more items.
     *
     * @throws Exception
     */
    private function removeItems(string $content): string
    {
        $crawler = new Crawler();
        $crawler->addHTMLContent($content, Yii::$app->charset);
        $crawler->filter($this->widgetItem)->each(function ($nodes) {
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        return $crawler->html();
    }

    /**
     * Generates a hashed variable to store the options.
     */
    private function hashOptions(): void
    {
        $this->_encodedOptions = Json::htmlEncode($this->_options);
        $this->_hashVar = self::WIDGET_NAME . '_' . hash('crc32', $this->_encodedOptions);
    }

    /**
     * Register the actual widget.
     */
    private function registerHashVarWidget(): bool
    {
        if (!isset(Yii::$app->params[self::WIDGET_NAME][$this->widgetContainer])) {
            Yii::$app->params[self::WIDGET_NAME][$this->widgetContainer] = $this->_hashVar;
            return true;
        }

        return false;
    }

    /**
     * Returns the hashed variable.
     */
    private function getHashVarName(): string
    {
        return Yii::$app->params[self::WIDGET_NAME][$this->widgetContainer] ?? $this->_hashVar;
    }

    /**
     * Registers plugin options by storing it in a hashed javascript variable.
     *
     * @param View $view The View object
     */
    private function registerOptions(View $view): void
    {
        $view->registerJs("var $this->_hashVar = $this->_encodedOptions;\n", $view::POS_HEAD);
    }

    /**
     * Registers the needed assets.
     *
     * @param View $view The View object
     */
    private function registerAssets(View $view): void
    {
        DynamicFormAsset::register($view);

        // add a click handler for the clone button
        $js = "jQuery('#$this->formId').on('click', '$this->insertButton', function(e) {\n";
        $js .= "    e.preventDefault();\n";
        $js .= "    jQuery('.$this->widgetContainer').triggerHandler('beforeInsert', [jQuery(this)]);\n";
        $js .= "    jQuery('.$this->widgetContainer').yiiDynamicForm('addItem', $this->_hashVar, e, jQuery(this));\n";
        $js .= "});\n";
        $view->registerJs($js);

        // add a click handler for the remove button
        $js = "jQuery('#$this->formId').on('click', '$this->deleteButton', function(e) {\n";
        $js .= "    e.preventDefault();\n";
        $js .= "    jQuery('.$this->widgetContainer').yiiDynamicForm('deleteItem', $this->_hashVar, e, jQuery(this));\n";
        $js .= "});\n";
        $view->registerJs($js);

        $js = "jQuery('#$this->formId').yiiDynamicForm($this->_hashVar);\n";
        $view->registerJs($js, $view::POS_LOAD);
    }
}