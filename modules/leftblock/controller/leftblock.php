<?php

/**
 * Left block
 *
 * @author Marcus Johansson <me @ marcusmailbox.com>
 * @version 0.10-beta
 */
class controllerLeftblock extends router
{
    /**
     * Creates the left block
	 * 
     * @return array Block arguments
     */	
    public function block_create()
    {
        $args = array('indexes' => array());

        // Get cluster state
        $state = self::$query_loader->call('_cluster/state', 'GET');

        foreach ($state['routing_table']['indices'] as $indexname => $data) {
            $index[$indexname]['shards'] = $data['shards'];
        }

        foreach ($state['metadata']['indices'] as $indexname => $data) {
            $index[$indexname]['up'] = array('red' => 0, 'green' => 0, 'yellow' => 0);
            $index[$indexname]['state'] = $data['state'];
            if (isset($index[$indexname]['shards'])) {
                foreach ($index[$indexname]['shards'] as $cluster) {
                    foreach ($cluster as $shard) {
                        switch ($shard['state']) {
                            case 'STARTED':
                                $index[$indexname]['up']['green']++;
                                break;
                            case 'UNASSIGNED':
                                $index[$indexname]['up']['yellow']++;
                                break;
                            default:
                                $index[$indexname]['up']['red']++;
                                break;
                        }
                    }
                }
            }
            $index[$indexname]['name'] = $indexname;
            $index[$indexname]['data'] = $data;

            $args['indexes'][$indexname] = $this->renderPart('leftblock_index', $index[$indexname]);
        }

        ksort($args['indexes']);

        $vars['content'] = $this->renderPart('leftblock', $args);
        $vars['title'] = 'Left Block';

        return $vars;
    }
}
