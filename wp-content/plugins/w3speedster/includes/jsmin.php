<?php
class W3jsMin {
    const ORD_LF            = 10;
    const ORD_SPACE         = 32;
    const ACTION_KEEP_A     = 1;
    const ACTION_DELETE_A   = 2;
    const ACTION_DELETE_A_B = 3;

    protected $a           = "\n";
    protected $b           = '';
    protected $input       = '';
    protected $inputIndex  = 0;
    protected $inputLength = 0;
    protected $lookAhead   = null;
    protected $output      = '';
    protected $lastByteOut  = '';
    protected $keptComment = '';

    public static function minify($js)
    {
        $jsmin = new W3jsMin($js);
        return $jsmin->min();
    }
    public function __construct($input)
    {
        $this->input = $input;
    }
    public function min()
    {
        if ($this->output !== '') { // min already run
            return $this->output;
        }

        $mbIntEnc = null;
        if (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2)) {
            $mbIntEnc = mb_internal_encoding();
            mb_internal_encoding('8bit');
        }

        if (isset($this->input[0]) && $this->input[0] === "\xef") {
            $this->input = substr($this->input, 3);
        }

        $this->input = str_replace("\r\n", "\n", $this->input);
        $this->inputLength = strlen($this->input);

        $this->action(self::ACTION_DELETE_A_B);

        while ($this->a !== null) {
            $command = self::ACTION_KEEP_A; // default
            if ($this->a === ' ') {
                if (($this->lastByteOut === '+' || $this->lastByteOut === '-')
                        && ($this->b === $this->lastByteOut)) {
                } elseif (! $this->isAlphaNum($this->b)) {
                    $command = self::ACTION_DELETE_A;
                }
            } elseif ($this->a === "\n") {
                if ($this->b === ' ') {
                    $command = self::ACTION_DELETE_A_B;
                } elseif ($this->b === null
                          || (false === strpos('{[(+-!~', $this->b)
                              && ! $this->isAlphaNum($this->b))) {
                    $command = self::ACTION_DELETE_A;
                }
            } elseif (! $this->isAlphaNum($this->a)) {
                if ($this->b === ' '
                    || ($this->b === "\n"
                        && (false === strpos('}])+-"\'', $this->a)))) {
                    $command = self::ACTION_DELETE_A_B;
                }
            }
            $this->action($command);
        }
        $this->output = trim($this->output);

        if ($mbIntEnc !== null) {
            mb_internal_encoding($mbIntEnc);
        }
        return $this->output;
    }

    protected function action($command)
    {
        if ($command === self::ACTION_DELETE_A_B
            && $this->b === ' '
            && ($this->a === '+' || $this->a === '-')) {
            if ($this->input[$this->inputIndex] === $this->a) {
                $command = self::ACTION_KEEP_A;
            }
        }

        switch ($command) {
            case self::ACTION_KEEP_A: // 1
                $this->output .= $this->a;

                if ($this->keptComment) {
                    $this->output = rtrim($this->output, "\n");
                    $this->output .= $this->keptComment;
                    $this->keptComment = '';
                }

                $this->lastByteOut = $this->a;

            case self::ACTION_DELETE_A: // 2
                $this->a = $this->b;
                if ($this->a === "'" || $this->a === '"' || $this->a === '`') { // string/template literal
                    $delimiter = $this->a;
                    $str = $this->a; // in case needed for exception
                    for(;;) {
                        $this->output .= $this->a;
                        $this->lastByteOut = $this->a;
                        $this->a = $this->get();
                        if ($this->a === $this->b) { // end quote
                            break;
                        }
                        if ($delimiter === '`' && $this->a === "\n") {
                            // leave the newline
                        } elseif ($this->isEOF($this->a)) {
                            $byte = $this->inputIndex - 1;
                            throw new W3JSMin_UnterminatedStringException(
                                "JSMin: Unterminated String at byte {$byte}: {$str}");
                        }
                        $str .= $this->a;
                        if ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->lastByteOut = $this->a;
                            $this->a = $this->get();
                            $str .= $this->a;
                        }
                    }
                }

            case self::ACTION_DELETE_A_B: // 3
                $this->b = $this->next();
                if ($this->b === '/' && $this->isRegexpLiteral()) {
                    $this->output .= $this->a . $this->b;
                    $pattern = '/'; // keep entire pattern in case we need to report it in the exception
                    for(;;) {
                        $this->a = $this->get();
                        $pattern .= $this->a;
                        if ($this->a === '[') {
                            for(;;) {
                                $this->output .= $this->a;
                                $this->a = $this->get();
                                $pattern .= $this->a;
                                if ($this->a === ']') {
                                    break;
                                }
                                if ($this->a === '\\') {
                                    $this->output .= $this->a;
                                    $this->a = $this->get();
                                    $pattern .= $this->a;
                                }
                                if ($this->isEOF($this->a)) {
                                    throw new W3JSMin_UnterminatedRegExpException(
                                        "JSMin: Unterminated set in RegExp at byte "
                                            . $this->inputIndex .": {$pattern}");
                                }
                            }
                        }

                        if ($this->a === '/') { // end pattern
                            break; // while (true)
                        } elseif ($this->a === '\\') {
                            $this->output .= $this->a;
                            $this->a = $this->get();
                            $pattern .= $this->a;
                        } elseif ($this->isEOF($this->a)) {
                            $byte = $this->inputIndex - 1;
                            throw new W3JSMin_UnterminatedRegExpException(
                                "JSMin: Unterminated RegExp at byte {$byte}: {$pattern}");
                        }
                        $this->output .= $this->a;
                        $this->lastByteOut = $this->a;
                    }
                    $this->b = $this->next();
                }
            // end case ACTION_DELETE_A_B
        }
    }

    protected function isRegexpLiteral()
    {
        if (false !== strpos("(,=:[!&|?+-~*{;", $this->a)) {
            // we can't divide after these tokens
            return true;
        }

        // check if first non-ws token is "/" (see starts-regex.js)
        $length = strlen($this->output);
        if ($this->a === ' ' || $this->a === "\n") {
            if ($length < 2) { // weird edge case
                return true;
            }
        }


        $subject = $this->output . trim($this->a);
        if (!preg_match('/(?:case|else|in|return|typeof)$/', $subject, $m)) {
            // not a keyword
            return false;
        }

        $charBeforeKeyword = substr($subject, 0 - strlen($m[0]) - 1, 1);
        if ($this->isAlphaNum($charBeforeKeyword)) {
            // this is really an identifier ending in a keyword, e.g. "xreturn"
            return false;
        }

        // it's a regexp. Remove unneeded whitespace after keyword
        if ($this->a === ' ' || $this->a === "\n") {
            $this->a = '';
        }

        return true;
    }

    protected function get()
    {
        $c = $this->lookAhead;
        $this->lookAhead = null;
        if ($c === null) {
            // getc(stdin)
            if ($this->inputIndex < $this->inputLength) {
                $c = $this->input[$this->inputIndex];
                $this->inputIndex += 1;
            } else {
                $c = null;
            }
        }
        if (ord($c) >= self::ORD_SPACE || $c === "\n" || $c === null) {
            return $c;
        }
        if ($c === "\r") {
            return "\n";
        }
        return ' ';
    }

    protected function isEOF($a)
    {
        return ord($a) <= self::ORD_LF;
    }

    protected function peek()
    {
        $this->lookAhead = $this->get();
        return $this->lookAhead;
    }

    protected function isAlphaNum($c)
    {
        return (preg_match('/^[a-z0-9A-Z_\\$\\\\]$/', $c) || ord($c) > 126);
    }

    protected function consumeSingleLineComment()
    {
        $comment = '';
        while (true) {
            $get = $this->get();
            $comment .= $get;
            if (ord($get) <= self::ORD_LF) { // end of line reached
                // if IE conditional comment
                if (preg_match('/^\\/@(?:cc_on|if|elif|else|end)\\b/', $comment)) {
                    $this->keptComment .= "/{$comment}";
                }
                return;
            }
        }
    }

    protected function consumeMultipleLineComment()
    {
        $this->get();
        $comment = '';
        for(;;) {
            $get = $this->get();
            if ($get === '*') {
                if ($this->peek() === '/') { // end of comment reached
                    $this->get();
                    if (0 === strpos($comment, '!')) {
                        // preserved by YUI Compressor
                        if (!$this->keptComment) {
                            // don't prepend a newline if two comments right after one another
                            $this->keptComment = "\n";
                        }
                        $this->keptComment .= "/*!" . substr($comment, 1) . "*/\n";
                    } else if (preg_match('/^@(?:cc_on|if|elif|else|end)\\b/', $comment)) {
                        // IE conditional
                        $this->keptComment .= "/*{$comment}*/";
                    }
                    return;
                }
            } elseif ($get === null) {
                throw new W3JSMin_UnterminatedCommentException(
                    "JSMin: Unterminated comment at byte {$this->inputIndex}: /*{$comment}");
            }
            $comment .= $get;
        }
    }

    protected function next()
    {
        $get = $this->get();
        if ($get === '/') {
            switch ($this->peek()) {
                case '/':
                    $this->consumeSingleLineComment();
                    $get = "\n";
                    break;
                case '*':
                    $this->consumeMultipleLineComment();
                    $get = ' ';
                    break;
            }
        }
        return $get;
    }
}

class W3JSMin_UnterminatedStringException extends Exception {}
class W3JSMin_UnterminatedCommentException extends Exception {}
class W3JSMin_UnterminatedRegExpException extends Exception {}
