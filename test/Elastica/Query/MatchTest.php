<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Match1;
use Elastica\Test\Base as BaseTest;

class MatchTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $field = 'test';
        $testQuery = 'Nicolas Ruflin';
        $operator = 'and';
        $analyzer = 'myanalyzer';
        $boost = 2.0;
        $minimumShouldMatch = 2;
        $fuzziness = 0.3;
        $fuzzyRewrite = 'constant_score_boolean';
        $prefixLength = 3;
        $maxExpansions = 12;

        $query = new Match1();
        $query->setFieldQuery($field, $testQuery);
        $this->hideDeprecated();
        $this->showDeprecated();
        $query->setFieldOperator($field, $operator);
        $query->setFieldAnalyzer($field, $analyzer);
        $query->setFieldBoost($field, $boost);
        $query->setFieldMinimumShouldMatch($field, $minimumShouldMatch);
        $query->setFieldFuzziness($field, $fuzziness);
        $query->setFieldFuzzyRewrite($field, $fuzzyRewrite);
        $query->setFieldPrefixLength($field, $prefixLength);
        $query->setFieldMaxExpansions($field, $maxExpansions);

        $expectedArray = [
            'match' => [
                $field => [
                    'query' => $testQuery,
                    'operator' => $operator,
                    'analyzer' => $analyzer,
                    'boost' => $boost,
                    'minimum_should_match' => $minimumShouldMatch,
                    'fuzziness' => $fuzziness,
                    'fuzzy_rewrite' => $fuzzyRewrite,
                    'prefix_length' => $prefixLength,
                    'max_expansions' => $maxExpansions,
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testMatch()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('_doc');

        $type->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'New Hampshire']),
            new Document(4, ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $field = 'name';
        $operator = 'or';

        $query = new Match1();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testMatchSetFieldBoost()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('_doc');

        $type->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'New Hampshire']),
            new Document(4, ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $field = 'name';
        $operator = 'or';

        $query = new Match1();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);
        $query->setFieldBoost($field, 1.2);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testMatchSetFieldBoostWithString()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('_doc');

        $type->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'New Hampshire']),
            new Document(4, ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $field = 'name';
        $operator = 'or';

        $query = new Match1();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);
        $query->setFieldBoost($field, '1.2');

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testMatchZeroTerm()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('_doc');

        $type->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
        ]);

        $index->refresh();

        $query = new Match1();
        $query->setFieldQuery('name', '');
        $query->setFieldZeroTermsQuery('name', Match1::ZERO_TERM_ALL);

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testMatchFuzzinessType()
    {
        $field = 'test';
        $query = new Match1();

        $fuzziness = 'AUTO';
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters = $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);

        $fuzziness = 0.3;
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters = $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);
    }

    /**
     * @group unit
     */
    public function testConstruct()
    {
        $match = new Match1(null, 'values');
        $this->assertEquals(['match' => []], $match->toArray());

        $match = new Match1('field', null);
        $this->assertEquals(['match' => []], $match->toArray());

        $match1 = new Match1('field', 'values');
        $match2 = new Match1();
        $match2->setField('field', 'values');
        $this->assertEquals($match1->toArray(), $match2->toArray());
    }
}
