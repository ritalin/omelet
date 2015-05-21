<?php

namespace Omelet\Annotation;

use Doctrine\Common\Annotations\DocLexer;
use Doctrine\Common\Annotations\AnnotationException;

class AnnotationFactory {
    /**
     * var string[]
     */
    private $uses;
    
    public function __construct(array $uses) {
        $this->uses = $uses;
    }
    
    public function getMethodAnnotations(\ReflectionMethod $method) {
        return $this->getAnnotations(
            $method->getDocComment(),
            "method: {$method->getDeclaringClass()->name}::{$method->name}"
        );
    }
    
    public function getAnnotations($comment, $context) {
        $annotations = $this->parse($comment, $context);

        return $annotations['params'] + $annotations['returns'] + $annotations['vars'];
    }

    public function parse($comment, $context) {
        $annotations = [
            'params' => [],
            'returns' => [],
            'vars' => [],
        ];
        
        $lexer = new DocLexer;
        $lexer->setInput($comment); 

        while (true) {
            $lexer->moveNext();
            $lexer->skipUntil(DocLexer::T_AT);
            if ($lexer->lookahead === null) break;
            
            if ($a = $this->parseReturnComment($lexer, $context)) {
                $annotations['returns'][] = $a;
            }
            if ($a = $this->parseParamComment($lexer, $context)) {
                $annotations['params'][] = $a;
            }
            if ($a = $this->parseVarComment($lexer, $context)) {
                $annotations['vars'][] = $a;
            }
        }
        
        if (count($annotations['returns']) > 1) {
            throw AnnotationException::semanticalError("Duplicated return: {$context}"); 
        }
        if (count($annotations['vars']) > 1) {
            throw AnnotationException::semanticalError("Duplicated var: {$context}"); 
        }
        
        return $annotations;
    }
     
     private function parseDocComment(DocLexer $lexer, $symbol, $repeatCount) {
        $lexer->resetPeek();
        
        if ((($token = $lexer->peek()) === null) || ($token['type'] !== DocLexer::T_IDENTIFIER) || ($token['value'] !== $symbol)) return false;

        $ids = [];
        $tk = $lexer->peek();
        while ($repeatCount-- > 0) {
            if (($tk === null) || ($tk['type'] !== DocLexer::T_IDENTIFIER)) return false;
            
            $tks = [$tk['value']];
            while (($tk = $lexer->peek()) && ($tk['type'] === DocLexer::T_NONE) && (in_array($tk['value'], ['[', ']']))) {
                $tks[] = $tk['value'];
            }
            
            $ids[] = implode('', $tks);
        }
        
        return $ids;
     }
     
     private function complementType($type, $context) {
        static $builtin = [
            'int', 'integer',
            'bool', 'boolean',
            'float', 'double',
            'string', 'array'
        ];
        
        if (($p = strrpos($type, '[]')) !== false) {
            return $this->complementType(substr($type, 0, $p), $context) . '[]';
        }
        if (in_array($type, $builtin)) return $type;
        
        foreach ($this->uses as $u) {
            $fqcn = $u . "\\" . $type;
            if (class_exists($fqcn)) return $fqcn;
        }
        
        if (class_exists($type)) {
            return $type;
        }
        
        throw AnnotationException::semanticalErrorConstants($type, $context); 
     }
     
     private function parseReturnComment(DocLexer $lexer, $context) {
        if ($ids = $this->parseDocComment($lexer, 'return', 1)) {
            return Returning::__set_state(['type' => $this->complementType($ids[0], $context)]);
        }
        else {
            return false;
        }
     }
     
     private function parseParamComment(DocLexer $lexer, $context) {
        if ($ids = $this->parseDocComment($lexer, 'param', 2)) {
            return ParamAlt::__set_state(['type' => $this->complementType($ids[0], $context), 'name' => $ids[1]]);
        }
        else {
            return false;
        }
     }
     
     private function parseVarComment(DocLexer $lexer, $context) {
        if ($ids = $this->parseDocComment($lexer, 'var', 1)) {
            return Column::__set_state(['type' => $this->complementType($ids[0], $context), 'name' => '']);
        }
        else {
            return false;
        }
     }
}
