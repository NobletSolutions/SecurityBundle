<?php
namespace NS\SecurityBundle\Doctrine;
use Doctrine\Common\EventArgs;

class SecurityListener
{
    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
                'prePersist',
                'onFlush',
                'loadClassMetadata'
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }

}
/*public function preDqlSelect(Doctrine_Event $event)
    {
        $invoker = $event->getInvoker();
        $class   = get_class($invoker);
        $params  = $event->getParams();
        $q       = $event->getQuery();
        $q_ps    = $q->getParams();

        $positional = gsQueryHelper::is_positional($q);

        $wheres  = array();
        $pars    = array();

        // only apply to the main protected table not chained tables... may break some situations
        if(!$this->contains('FROM '.$class, $q))
            return;

        $from = $q->getDqlPart('from');

        foreach($this->_options['conditions'] as $rel_name => $conditions)
        {
            $apply = false;

            foreach($conditions['apply_to'] as $val)
            {
                if(in_array($val,self::$_credentials))
                {
                    $apply = true;
                    break;
                }
            }

            if($apply)
            {
                $alias     = $params['alias'];
                $through   = $this->adjustConditions($conditions['through'],$params, $q, $alias);
                $aliases   = array();
                $aliases[] = $alias;

                foreach($through as $key => $table)
                {
                    $index = 0;
                    $newalias = strtolower(substr($table,0,3)).self::$_alias_count++;
                    $q->leftJoin(end($aliases).'.'.$table.' '.$newalias);
                    $aliases[] = $newalias;
                }

                if($positional)
                {
                    $wheres[]    = '('.end($aliases).'.'.$conditions['field'].' = '.self::$_user_id.' )';
                    //$pars[]      = self::$_user_id;
                }
                else
                {
                    $key         = ':'.rand(0,50).'_'.end($aliases).'_'.$conditions['field'];
                    $wheres[]    = '('.end($aliases).'.'.$conditions['field'].' = '.$key.' )';
                    $pars[$key]  = self::$_user_id;
                }
            }
        }

        if(!empty($wheres))
            $q->addWhere( '('.implode(' OR ',$wheres).')',$pars);

    }

    protected function contains($crit, $query)
    {
        if(strpos($query->getDql(),"$crit ") !== false || strpos($query->getDql(),$crit) !== false)
            return true;
        else
            return false;
    }

    protected function adjustConditions($conditions, $params, $query,&$new_alias)
    {
        $from_dql       = $query->getDqlPart('from');
        $new_conditions = array();

        foreach($conditions as $key => $table)
        {
            if($this->contains($table,$query)) // found existing table.
            {
                foreach($from_dql as $part)
                {
                    if($pos = strpos($part,$table))
                    {
                        $start     = $pos+strlen($table)+1;
                        $t         = strpos($part,' ',$start);
                        $len       = ($t !== false) ? $t-$start:null;
                        $new_alias = ($len) ? substr($part,$start,$len): substr($part,strrpos($part,' '));

                        unset($conditions[$key]);

                        break;
                    }
                }

                break;
            }
        }

        return ($conditions);
    }
*/