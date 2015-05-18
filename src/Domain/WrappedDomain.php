<?php

namespace Omelet\Domain;

class WrappedDomain extends DomainBase {
    protected function expandTypesInternal($name, $val) {
        return ($val instanceof CustomDomain) ? $val->expandTypes($name, $val, false) : [];
    }

    protected function expandValuesInternal($name, $val) {
        return ($val instanceof CustomDomain) ? $val->expandValues($name, $val, false) : [];
    }
}
