<?php

namespace Pina;

interface TranslatorInterface
{
    public function translate($code, $term);
}