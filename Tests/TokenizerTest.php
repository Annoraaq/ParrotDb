<?php
namespace ParrotDb\Query\LotB;

use \ParrotDb\Query\LotB\Ebnf\Symbol;
use \ParrotDb\Query\LotB\Ebnf\NonTerminal;
use \ParrotDb\Query\LotB\Ebnf\Terminal;
use \ParrotDb\Query\LotB\Ebnf\OptionalSymbol;
use \ParrotDb\Query\LotB\Ebnf\OrSymbol;
use \ParrotDb\Query\LotB\Ebnf\PlusSymbol;
use \ParrotDb\Query\LotB\Ebnf\StarSymbol;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-26 at 11:44:05.
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Tokenizer
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers ParrotDb\Query\LotB\Tokenizer::tokenize
     * @todo   Implement testTokenize().
     */
    public function testTokenize()
    {
         $get = new Terminal("get");
        
        $letters['a'] = new Terminal('a');
        $letters['b'] = new Terminal('b');
        $letters['c'] = new Terminal('c');
        $letters['d'] = new Terminal('d');
        $letters['e'] = new Terminal('e');
        $letters['f'] = new Terminal('f');
        $letters['g'] = new Terminal('g');
        $letters['h'] = new Terminal('h');
        $letters['i'] = new Terminal('i');
        $letters['j'] = new Terminal('j');
        $letters['k'] = new Terminal('k');
        $letters['l'] = new Terminal('l');
        $letters['m'] = new Terminal('m');
        $letters['n'] = new Terminal('n');
        $letters['o'] = new Terminal('o');
        $letters['p'] = new Terminal('p');
        $letters['q'] = new Terminal('q');
        $letters['r'] = new Terminal('r');
        $letters['s'] = new Terminal('s');
        $letters['t'] = new Terminal('t');
        $letters['u'] = new Terminal('u');
        $letters['v'] = new Terminal('v');
        $letters['w'] = new Terminal('w');
        $letters['x'] = new Terminal('x');
        $letters['y'] = new Terminal('y');
        $letters['z'] = new Terminal('z');
        $letters['A'] = new Terminal('A');
        $letters['B'] = new Terminal('B');
        $letters['C'] = new Terminal('C');
        $letters['D'] = new Terminal('D');
        $letters['E'] = new Terminal('E');
        $letters['F'] = new Terminal('F');
        $letters['G'] = new Terminal('G');
        $letters['H'] = new Terminal('H');
        $letters['I'] = new Terminal('I');
        $letters['J'] = new Terminal('J');
        $letters['K'] = new Terminal('K');
        $letters['L'] = new Terminal('L');
        $letters['M'] = new Terminal('M');
        $letters['N'] = new Terminal('N');
        $letters['O'] = new Terminal('O');
        $letters['P'] = new Terminal('P');
        $letters['Q'] = new Terminal('Q');
        $letters['R'] = new Terminal('R');
        $letters['S'] = new Terminal('S');
        $letters['T'] = new Terminal('T');
        $letters['U'] = new Terminal('U');
        $letters['V'] = new Terminal('V');
        $letters['W'] = new Terminal('W');
        $letters['X'] = new Terminal('X');
        $letters['Y'] = new Terminal('Y');
        $letters['Z'] = new Terminal('Z');
        
        $numbers['0'] = new Terminal('0');
        $numbers['1'] = new Terminal('1');
        $numbers['2'] = new Terminal('2');
        $numbers['3'] = new Terminal('3');
        $numbers['4'] = new Terminal('4');
        $numbers['5'] = new Terminal('5');
        $numbers['6'] = new Terminal('6');
        $numbers['7'] = new Terminal('7');
        $numbers['8'] = new Terminal('8');
        $numbers['9'] = new Terminal('9');
        
        $number = new OrSymbol();
        foreach ($numbers as $num) {
            $number->addSymbol($num);
        }
        
        $program = new NonTerminal();
        $program->addSymbol($get);
        
        $letter = new OrSymbol();
        foreach ($letters as $let) {
            $letter->addSymbol($let);
        }
        
        $nt = new NonTerminal();
        $nt->addSymbol($letter);
        $ot = new OrSymbol();
        $ot->addSymbol($letter);
        $ot->addSymbol($number);
        $nt->addSymbol(new StarSymbol($ot));
        $className = new MinusSymbol($nt, $get);
        $program->addSymbol($className);
        
        var_dump($program);
        
        $this->object = new Tokenizer("get Author");
        $arr = $this->object->tokenize();
        $program->parse($arr);
        

    }
}
