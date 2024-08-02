<?php


namespace Pina\Types;

use Pina\App;
use Pina\Html;

use function Pina\__;

class URLType extends StringType
{

    public function normalize($value, $isMandatory)
    {
        $value = parent::normalize($value, $isMandatory);

        //проверка соответствия isMandatory и $value происходит выше
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            throw new ValidateException(__("Укажите корректный URL"));
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function play($value): string
    {
        $options = [];
        if ($this->isExternalLink($value ?? '')) {
            $options['target'] = '_blank';
        }
        return Html::a($value, $value, $options);
    }

    protected function isExternalLink(string $link)
    {
        $host = parse_url($link, PHP_URL_HOST);
        return !empty($host) && $host != App::host();
    }

}