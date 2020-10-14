<?php
/**
 * TurmasDoPolo REST service
 */
class TurmasDoPoloService extends AdiantiRecordService
{
    const DATABASE      = 'ppconnect';
    const ACTIVE_RECORD = 'TurmasDoPolo';
    
    /**
     * Autentica o código do servidor para ler as turmas do polo
     * devolve uma lista com as turmas do servidor que possui o código correto
     */
    public function loadTurmasDoPolo($param)
    {
        $database     = static::DATABASE;
        $activeRecord = static::ACTIVE_RECORD;
        
        TTransaction::open($database);        
        $equipamentos  = EquipamentosDoPolo::where('codigo', '=', $param['codigo'])->load();
        TTransaction::close();
       
        if ($equipamentos)
        {
            foreach ($equipamentos as $equipamento){
                $param['filters'] = [['polos_id', '=', $equipamento->polos_id]];
                return $this->loadAll($param);
            }
        }
        return null;
    }
    
    /**
     * load($param)
     *
     * Load an Active Records by its ID
     * 
     * @return The Active Record as associative array
     * @param $param['id'] Object ID
     */
    
    
    /**
     * delete($param)
     *
     * Delete an Active Records by its ID
     * 
     * @return The Operation result
     * @param $param['id'] Object ID
     */
    
    
    /**
     * store($param)
     *
     * Save an Active Records
     * 
     * @return The Operation result
     * @param $param['data'] Associative array with object data
     */
    
    
    /**
     * loadAll($param)
     *
     * List the Active Records by the filter
     * 
     * @return Array of records
     * @param $param['offset']    Query offset
     *        $param['limit']     Query limit
     *        $param['order']     Query order by
     *        $param['direction'] Query order direction (asc, desc)
     *        $param['filters']   Query filters (array with field,operator,field)
     */
    
    
    /**
     * deleteAll($param)
     *
     * Delete the Active Records by the filter
     * 
     * @return Array of records
     * @param $param['filters']   Query filters (array with field,operator,field)
     */
}
