<?php
/**
 * SystemUser REST service
 */
class SystemUserService extends AdiantiRecordService
{
    const DATABASE      = 'ppconnect';
    const ACTIVE_RECORD = 'SystemUser';
    
    /**
     * load($param)
     *
     * Load an Active Records by its ID
     * 
     * @return The Active Record as associative array
     * @param $param['id'] Object ID
     */
     
    public function loadTutores($param)
    {
        $database     = static::DATABASE;
        $activeRecord = static::ACTIVE_RECORD;
        
        TTransaction::open($database);        
        $repositorio = new TRepository('EquipamentosDoPolo'); 
        $criterio = new TCriteria;
        $criterio->add(new TFilter ('codigo', '=', $param['codigo']));//$param('codigo')));                
        $equipamentos = $repositorio->load($criterio);

        TTransaction::close();
       
        if ($equipamentos)
        {
            foreach ($equipamentos as $equipamento){
                $param['filters'] = [['polos_id', '=', $equipamento->polos_id], ['aluno','=','N']];
                return $this->loadAll($param);
            }
        }
        return null;

    }
    
    
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
