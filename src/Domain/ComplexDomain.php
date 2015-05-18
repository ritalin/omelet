<?php

namespace Omelet\Domain;

class ComplexDomain extends DomainBase {
    private $domains;
    
    public function __construct(array $domains) {
        $this->domains = $domains;
    }
    
    public function getChildren() {
        return $this->domains;
    }
    
    protected function expandTypesInternal($name, $val) {
        return array_reduce(
            array_keys($this->domains),
            function (array &$tmp, $k) use($val) {
                return $tmp + [$k => $this->domains[$k]->expandTypes($k, $val[$k])];
            },
            []
        );
    }
    
    protected function expandValuesInternal($name, $val) {
        return array_reduce(
            array_keys($this->domains),
            function (array &$tmp, $k) use($val) {
                return $tmp + [$k => $this->domains[$k]->expandValues($k, $val[$k])];
            },
            []
        );
    }
}
