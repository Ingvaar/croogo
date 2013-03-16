<?php
App::uses('Node', 'Nodes.Model');
App::uses('CroogoTestCase', 'Croogo.TestSuite');

class NodeTest extends CroogoTestCase {

	public $fixtures = array(
		'plugin.croogo.aco',
		'plugin.croogo.aro',
		'plugin.croogo.aros_aco',
		'plugin.blocks.block',
		'plugin.comments.comment',
		'plugin.contacts.contact',
		'plugin.translate.i18n',
		'plugin.settings.language',
		'plugin.menus.link',
		'plugin.menus.menu',
		'plugin.contacts.message',
		'plugin.meta.meta',
		'plugin.nodes.node',
		'plugin.taxonomy.nodes_taxonomy',
		'plugin.blocks.region',
		'plugin.users.role',
		'plugin.settings.setting',
		'plugin.taxonomy.taxonomy',
		'plugin.taxonomy.term',
		'plugin.taxonomy.type',
		'plugin.taxonomy.types_vocabulary',
		'plugin.users.user',
		'plugin.taxonomy.vocabulary',
	);

	public function setUp() {
		parent::setUp();
		$this->Node = ClassRegistry::init('Nodes.Node');
		$this->Node->Behaviors->unload('Acl');
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Node);
	}

	public function testCacheTerms() {
		$this->Node->data = array(
			'Node' => array(),
			'Taxonomy' => array(
				'Taxonomy' => array(1, 2), // uncategorized, and announcements
			),
		);
		$this->Node->cacheTerms();
		$this->assertEqual($this->Node->data['Node']['terms'], '{"1":"uncategorized","2":"announcements"}');
	}

	public function testNodeDeleteDependent() {
		// assert existing count
		$commentCount = $this->Node->Comment->find('count',
			array('conditions' => array('Comment.node_id' => 1))
			);
		$this->assertEquals(2, $commentCount);

		$metaCount = $this->Node->Meta->find('count',
			array('conditions' => array('model' => 'Node', 'foreign_key' => 1))
			);
		$this->assertEquals(1, $metaCount);

		// delete node
		$this->Node->id = 1;
		$this->Node->delete();

		$commentCount = $this->Node->Comment->find('count',
			array('conditions' => array('Comment.node_id' => 1))
			);
		$this->assertEqual(0, $commentCount);

		$metaCount = $this->Node->Meta->find('count',
			array('conditions' => array('model' => 'Node', 'foreign_key' => 1))
			);
		$this->assertEqual(0, $metaCount);
	}

/**
 * test saving node.
 */
	public function testAddNode(){
		$this->Node->Behaviors->disable('Tree');
		$oldNodeCount = $this->Node->find('count');

		$data = array(
			'title' => 'Test Content',
			'slug' => 'test-content',
			'type' => 'blog',
			'token_key' => 1,
			'body' => '',
		);
		$result = $this->Node->saveNode($data, Node::DEFAULT_TYPE);
		$newNodeCount = $this->Node->find('count');

		$this->assertTrue($result);
		$this->assertTrue($this->Node->Behaviors->enabled('Tree'));
		$this->assertEquals($oldNodeCount + 1, $newNodeCount);
	}

/**
 * testAddNodeWithTaxonomyData
 */
	public function testAddNodeWithTaxonomyData(){
		$oldNodeCount = $this->Node->find('count');

		$data = array(
			'Node' => array(
				'title' => 'Test Content',
				'slug' => 'test-content',
				'type' => 'blog',
				'token_key' => 1,
				'body' => '',
			),
			'TaxonomyData' => array(1 => array(0 => '1', 1 => '2'))
		);
		$result = $this->Node->saveNode($data, Node::DEFAULT_TYPE);
		$newNodeCount = $this->Node->find('count');

		$this->assertTrue($result);
		$this->assertEquals($oldNodeCount + 1, $newNodeCount);
	}

/**
 * testAddNodeWithVisibilityRole
 */
	public function testAddNodeWithVisibilityRole(){
		$oldNodeCount = $this->Node->find('count');

		$data = array(
			'Node' => array(
				'title' => 'Test Content',
				'slug' => 'test-content',
				'type' => 'blog',
				'token_key' => 1,
				'body' => '',
			),
			'Role' => array('Role' => array('3')) //Public
		);
		$result = $this->Node->saveNode($data, Node::DEFAULT_TYPE);
		$newNodeCount = $this->Node->find('count');

		$this->assertTrue($result);
		$this->assertEquals($oldNodeCount + 1, $newNodeCount);
	}

/**
 * testAddNodeWithInvalidNodeType
 */
	public function testAddNodeWithInvalidNodeType(){
		$this->setExpectedException('InvalidArgumentException');
		$data = array(
			'title' => 'Test Content',
			'slug' => 'test-content',
			'type' => 'invalid',
			'token_key' => 1,
			'body' => '',
		);
		$result = $this->Node->saveNode($data, 'invalid');
	}

/**
 * Test filtering methods
 */
	public function testFilterNodesByTitle(){
		$filterConditions = $this->Node->filterNodes(array('filter' => 'Hello'));
		$node = $this->Node->find('first', array('conditions' => $filterConditions));

		$this->assertNotEmpty($node);
		$this->assertEquals(1, $node['Node']['id']);
	}

	public function testFilterNodesByBody(){
		$filterConditions = $this->Node->filterNodes(array('filter' => 'example'));
		$node = $this->Node->find('first', array('conditions' => $filterConditions));

		$this->assertNotEmpty($node);
		$this->assertEquals(2, $node['Node']['id']);
	}

	public function testFilterNodesWithoutKeyword(){
		$filterConditions = $this->Node->filterNodes();
		$nodes = $this->Node->find('all', array('conditions' => $filterConditions));

		$this->assertEquals(2, count($nodes));
	}

/**
 * test updateAllNodesPaths
 */
	public function testUpdateAllNodesPaths(){
		$node = $this->Node->findById(1);
		$node['Node']['path'] = 'invalid one';
		$this->assertTrue((bool) $this->Node->save($node));

		CroogoRouter::contentType('blog');
		$this->assertTrue($this->Node->updateAllNodesPaths());
		$node = $this->Node->findById(1);
		$this->assertEquals('/blog/hello-world', $node['Node']['path']);
	}

/**
 * Test find('promoted')
 */
	public function testFindPromoted(){
		$results = $this->Node->find('promoted');
		$expectedId = 1;

		$this->assertEquals(1, count($results));
		$this->assertEquals($expectedId, $results[0]['Node']['id']);
		$this->assertEquals(Node::STATUS_PUBLISHED, $results[0]['Node']['status']);
		$this->assertEquals(Node::STATUS_PROMOTED, $results[0]['Node']['promote']);
	}

}
