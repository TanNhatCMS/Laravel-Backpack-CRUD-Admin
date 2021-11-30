<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\Star;
use Backpack\CRUD\Tests\Unit\Models\Bill;
use Backpack\CRUD\Tests\Unit\Models\Recommend;
use Backpack\CRUD\Tests\Unit\Models\Article;
use Backpack\CRUD\Tests\Unit\Models\User;
use Faker\Factory;
use Illuminate\Support\Arr;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Create
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Relationships
 */
class CrudPanelCreateTest extends BaseDBCrudPanelTest
{
    private $nonRelationshipField = [
        'name'  => 'field1',
        'label' => 'Field1',
    ];

    private $userInputFieldsNoRelationships = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'name',
        ], [
            'name' => 'email',
            'type' => 'email',
        ], [
            'name' => 'password',
            'type' => 'password',
        ],
    ];

    private $articleInputFieldsOneToMany = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'content',
        ], [
            'name' => 'tags',
        ], [
            'label'     => 'Author',
            'type'      => 'select',
            'name'      => 'user_id',
            'entity'    => 'user',
            'attribute' => 'name',
        ],
    ];

    private $userInputFieldsManyToMany = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'name',
        ], [
            'name' => 'email',
            'type' => 'email',
        ], [
            'name' => 'password',
            'type' => 'password',
        ], [
            'label'     => 'Roles',
            'type'      => 'select_multiple',
            'name'      => 'roles',
            'entity'    => 'roles',
            'attribute' => 'name',
            'pivot'     => true,
        ],
    ];

    private $userInputFieldsDotNotation = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'name',
        ], [
            'name' => 'email',
            'type' => 'email',
        ], [
            'name' => 'password',
            'type' => 'password',
        ], [
            'label'     => 'Roles',
            'type'      => 'relationship',
            'name'      => 'roles',
            'entity'    => 'roles',
            'attribute' => 'name',
        ], [
            'label'     => 'Street',
            'name'      => 'street',
            'entity'    => 'accountDetails.addresses',
            'attribute' => 'street',
        ],
    ];

    private $userInputHasOneRelation = [
        [
            'name' => 'accountDetails.nickname',
        ],
        [
            'name' => 'accountDetails.profile_picture',
        ],
    ];

    private $articleInputBelongsToRelationName = [
        [
            'name' => 'user',
        ],
    ];

    public function testCreate()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $faker = Factory::create();
        $inputData = [
            'name'     => $faker->name,
            'email'    => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEmpty($entry->articles);
    }

    public function testCreateWithOneToOneRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addFields($this->userInputHasOneRelation);
        $faker = Factory::create();
        $account_details_nickname = $faker->name;
        $inputData = [
            'name'     => $faker->name,
            'email'    => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
            'accountDetails' => [
                'nickname' => $account_details_nickname,
                'profile_picture' => 'test.jpg',
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->nickname, $account_details_nickname);
    }

    public function testCreateBelongsToWithRelationName()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addFields($this->articleInputFieldsOneToMany);
        $this->crudPanel->removeField('user_id');
        $this->crudPanel->addFields($this->articleInputBelongsToRelationName);
        $faker = Factory::create();
        $inputData = [
            'content'     => $faker->text(),
            'tags'        => $faker->words(3, true),
            'user'     => 1,
            'metas'       => null,
            'extras'      => null,
            'cast_metas'  => null,
            'cast_tags'   => null,
            'cast_extras' => null,
        ];
        $entry = $this->crudPanel->create($inputData);
        $userEntry = User::find(1);
        $article = Article::where('user_id', 1)->with('user')->get()->last();
        $this->assertEquals($article->user_id, $entry->user_id);
        $this->assertEquals($article->id, $entry->id);
    }

    public function testCreateWithOneToManyRelationship()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addFields($this->articleInputFieldsOneToMany);
        $faker = Factory::create();
        $inputData = [
            'content'     => $faker->text(),
            'tags'        => $faker->words(3, true),
            'user_id'     => 1,
            'metas'       => null,
            'extras'      => null,
            'cast_metas'  => null,
            'cast_tags'   => null,
            'cast_extras' => null,
        ];

        $entry = $this->crudPanel->create($inputData);
        $userEntry = User::find(1);
        $article = Article::where('user_id', 1)->with('user')->get()->last();
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEquals($article->user_id, $entry->user_id);
        $this->assertEquals($article->id, $entry->id);
    }

    public function testCreateWithManyToManyRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
    }
    
    public function testBelongsToManyWithPivotDataRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField([
            'name' => 'superArticles',
            'pivotFields' => [
                [
                    'name' => 'notes',
                ]
            ]
        ]);

        $faker = Factory::create();
        $articleData = [
            'content'     => $faker->text(),
            'tags'        => $faker->words(3, true),
            'user_id'     => 1,
        ];

        $article = Article::create($articleData);

        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'superArticles'          => [
                [
                    'superArticles' => $article->id,
                    'notes' => 'my first article note',
                ]
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        
        $this->assertCount(1, $entry->fresh()->superArticles);
        $this->assertEquals('my first article note', $entry->fresh()->superArticles->first()->pivot->notes);
    }

    public function testGetRelationFields()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany, 'create');

        // TODO: fix method and documentation. when 'both' is passed as the $form value, the getRelationFields searches
        //       for relationship fields in the update fields.
        $relationFields = $this->crudPanel->getRelationFields('both');

        $this->assertEquals($this->crudPanel->create_fields['roles'], Arr::last($relationFields));
    }

    public function testGetRelationFieldsCreateForm()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);

        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEquals($this->crudPanel->get('create.fields')['roles'], Arr::last($relationFields));
    }

    public function testGetRelationFieldsUpdateForm()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('update');
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);

        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEquals($this->crudPanel->get('update.fields')['roles'], Arr::last($relationFields));
    }

    public function testGetRelationFieldsUnknownForm()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->expectException(\InvalidArgumentException::class);

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);

        // TODO: this should throw an invalid argument exception but instead it searches for relationship fields in the
        //       update fields.
        $this->crudPanel->getRelationFields('unknownForm');
    }

    public function testGetRelationFieldsDotNotation()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $this->crudPanel->addFields($this->userInputFieldsDotNotation);

        //get all fields with a relation
        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEquals($this->crudPanel->get('create.fields')['street'], Arr::last($relationFields));
    }

    public function testCreateHasOneRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $this->crudPanel->addFields($this->userInputHasOneRelation);
        $faker = Factory::create();

        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
            'accountDetails' => [
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'simple_picture.jpg',
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->nickname, 'i_have_has_one');
    }
   
    public function testCreateHasOneWithNestedRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $this->crudPanel->addFields([
            [
                'name' => 'accountDetails.nickname',
            ],
            [
                'name' => 'accountDetails.profile_picture',
            ],
            [
                'name' => 'accountDetails.article',
            ]
        ]);

        $faker = Factory::create();

        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
            'accountDetails' => [
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'ohh my picture 1.jpg',
                'article' => 1
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->article, Article::find(1));
    }

    public function testCreateHasOneWithNestedBelongsToKeyRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $this->crudPanel->addFields([
            [
                'name' => 'accountDetails.nickname',
            ],
            [
                'name' => 'accountDetails.profile_picture',
            ],
            [
                'name' => 'accountDetails.article_id',
            ]
        ]);

        $faker = Factory::create();

        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
            'accountDetails' => [
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'ohh my picture 1.jpg',
                'article_id' => 1
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->article, Article::find(1));
    }

    public function testGetRelationFieldsNoRelations()
    {
        $this->crudPanel->addField($this->nonRelationshipField);

        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEmpty($relationFields);
    }

    public function testGetRelationFieldsNoFields()
    {
        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEmpty($relationFields);
    }

    public function testGetRelationFieldsWithPivot()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsDotNotation);

        $relationFields = $this->crudPanel->getRelationFieldsWithPivot();
        $this->assertEquals($this->crudPanel->get('create.fields')['roles'], Arr::first($relationFields));
    }

    public function testGetRelationFieldsWithPivotNoRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->nonRelationshipField);

        $relationFields = $this->crudPanel->getRelationFieldsWithPivot();

        $this->assertEmpty($relationFields);
    }

    public function testMorphOneRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'comment.text'
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'comment'          => [
                'text' => 'some test comment text'
            ],
        ];

        
        $entry = $this->crudPanel->create($inputData);

        $this->assertEquals($inputData['comment']['text'], $entry->comment->text);

        $inputData['comment']['text'] = 'updated comment text';

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertEquals($inputData['comment']['text'], $entry->fresh()->comment->text);

    }

    public function testMorphManyWithPivotRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
                'name'    => 'stars',
                'pivotFields' => [
                    [
                        'name' => 'title',
                    ],
                ],
            ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'stars'          => [
                [
                    'title' => 'this is the star 1 title'
                ],
                [
                    'title' => 'this is the star 2 title'
                ],
            ],
        ];
        
        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(2, $entry->stars);

        $this->assertEquals($inputData['stars'][0]['title'], $entry->stars()->first()->title);

        $inputData['stars'] = [
            [
                'title' => 'only one star with changed title'
            ]
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->stars);

        $this->assertEquals($inputData['stars'][0]['title'], $entry->fresh()->stars->first()->title);

    }

    public function testMorphToManySelectableRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField(['name' => 'bills'], 'both');

        $bill1 = Bill::create([
            'title' => 'first bill',
        ]);

        $bill2 = Bill::create([
            'title' => 'second bill',
        ]);

        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'bills'          => [$bill1->id],
        ];
        
        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(1, $entry->bills);

        $this->assertEquals($bill1->id, $entry->bills()->first()->id);

        $inputData['bills'] = [$bill1->id, $bill2->id];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(2, $entry->fresh()->bills);

        $this->assertEquals([$bill1->id, $bill2->id], $entry->fresh()->bills->pluck('id')->toArray());

    }
    
    public function testMorphToManyCreatableRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField(['name' => 'recommends', 'pivotFields' => [
            [
                'name' => 'text'
            ]
        ]], 'both');

        $recommend1 = Recommend::create([
            'title' => 'recommend 1',
        ]);

        $recommend2 = Recommend::create([
            'title' => 'recommend2',
        ]);

        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'recommends'          => [
                [
                    'recommends' => $recommend1->id,
                    'text' => 'my pivot recommend field'
                ]
            ]
        ];
        
        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(1, $entry->recommends);

        $this->assertEquals($recommend1->id, $entry->recommends()->first()->id);

        $inputData['recommends'] = [
            [
                'recommends' => $recommend2->id,
                'text' => 'I changed the recommend and the pivot text'
            ]
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->recommends);

        $this->assertEquals($recommend2->id, $entry->recommends()->first()->id);
 
        $this->assertEquals('I changed the recommend and the pivot text', $entry->fresh()->recommends->first()->pivot->text);

    }

    

    



    public function testManyToManyPivotSync()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = User::find(1);
        $this->crudPanel->update($entry->id, $inputData);

        $this->assertEquals($inputData['roles'], $entry->roles->pluck('id')->toArray());
    }

    public function testManyToManySyncPivotNotData()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->nonRelationshipField);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = User::find(1);
        $this->crudPanel->update($entry->id, $inputData);

        $this->assertEquals(1, $entry->roles->count());
    }

    public function testSyncPivotUnknownModel()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = Article::find(1);
        $this->crudPanel->syncPivot($entry, $inputData);
    }
}
