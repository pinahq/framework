<?php

namespace Pina\Types;

use Pina\Controls\Control;

interface TypeInterface {
    
    /**
     * @return Control 
     */
    public function makeControl();
    
    /**
     * @return int
     */
    public function getSize();
    
    /**
     * @return mixed
     */
    public function getDefault();
    
    /**
     * @return bool
     */
    public function isNullable();
    
    /*
     * @return array
     */
    public function getVariants();
}