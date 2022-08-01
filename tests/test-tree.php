<?php
require_once __DIR__ . '/../class-rusty-inc-org-chart-tree.php';

class Rusty_Inc_Org_Chart_Tree_Test extends WP_UnitTestCase
{

	public function test_empty_list_returns_null()
	{
		$this->assertEquals(null, (new Rusty_Inc_Org_Chart_Tree([]))->get_nested_tree());
	}

	public function test_only_root_returns_single_node()
	{
		$only_root = [
			[
				'id'        => 1,
				'name'      => 'root',
				'parent_id' => null,
			],
		];
		$expected  = array_merge($only_root[0], ['children' => []]);
		$this->assertEquals($expected, (new Rusty_Inc_Org_Chart_Tree($only_root))->get_nested_tree());
	}

	public function test_three_levels_deep_structure()
	{
		$list_of_teams = [
			[
				'id'        => 2,
				'name'      => 'Food',
				'emoji'     => '🥩',
				'parent_id' => 1,
			],
			[
				'id'        => 4,
				'name'      => 'Massages',
				'emoji'     => '💆',
				'parent_id' => 3,
			],
			[
				'id'        => 3,
				'name'      => 'Canine Therapy',
				'emoji'     => '😌',
				'parent_id' => 1,
			],
			[
				'id'        => 5,
				'name'      => 'Games',
				'emoji'     => '🎾',
				'parent_id' => 3,
			],
			[
				'id'        => 1,
				'name'      => 'Rusty Corp.',
				'emoji'     => '🐕',
				'parent_id' => null,
			],
		];
		$expected      = [
			'id'        => 1,
			'name'      => 'Rusty Corp.',
			'emoji'     => '🐕',
			'parent_id' => null,
			'children'  => [
				[
					'id'        => 2,
					'name'      => 'Food',
					'emoji'     => '🥩',
					'parent_id' => 1,
					'children'  => [],
				],
				[
					'id'        => 3,
					'name'      => 'Canine Therapy',
					'emoji'     => '😌',
					'parent_id' => 1,
					'children'  => [
						[
							'id'        => 4,
							'name'      => 'Massages',
							'emoji'     => '💆',
							'parent_id' => 3,
							'children'  => [],
						],
						[
							'id'        => 5,
							'name'      => 'Games',
							'emoji'     => '🎾',
							'parent_id' => 3,
							'children'  => [],
						],
					],
				],
			],
		];
		$this->assertEquals($expected, (new Rusty_Inc_Org_Chart_Tree($list_of_teams))->get_nested_tree());
	}

	public function test_secret_key_generation()
	{
		$plugin = new Rusty_Inc_Org_Chart_Sharing();
		$oldSecretKey =  $plugin->key();

		//generate new secret key
		$plugin->regenerate_key();

		$newSecretKey =  $plugin->key();
		$expected  = $oldSecretKey;
		$this->assertNotEquals($expected, $newSecretKey);
	}

	public function test_tree_update()
	{
		$plugin = new Rusty_Inc_Org_Chart_Plugin();

		$newTree = '{"id":1,"name":"Rusty Corp.","emoji":"🐕","parent_id":null,"children":[{"id":2,"name":"Food","emoji":"🥩","parent_id":1,"children":[]}]}';

		if (!get_option($plugin::OPTION_NAME)) {
			add_option($plugin::OPTION_NAME, $newTree);
		} else {
			update_option($plugin::OPTION_NAME, $newTree);
		}

		$expected  = get_option("rusty-inc-org-chart-tree");;
		$this->assertEquals($expected, $newTree);
	}



	public function test_valid_secret_url()
	{
		$plugin = new Rusty_Inc_Org_Chart_Sharing();

		$secretKey =  $plugin->key();
		$secretUrl =  $plugin->url();
		$this->assertStringContainsString($secretKey, $secretUrl);
	}
}
