<?php
/**
 * QuestoesDasProvasGeradas REST service
 */
class QuestoesDasProvasGeradasService extends AdiantiRecordService
{
    const DATABASE      = 'ppconnectpolo';
    const ACTIVE_RECORD = 'QuestoesDasProvasGeradas';
    
    
    public function storeimagem($param)
    {
        $database     = static::DATABASE;
        $activeRecord = static::ACTIVE_RECORD;
       
        $param['data'] = ['id' => 39, 'imagem' => $param['img']];
        return $this->store($param);
    }


    public function storevideo($param)
    {
        $database     = static::DATABASE;
        $activeRecord = static::ACTIVE_RECORD;
       
        $param['data'] = ['id' => 39, 'video' => $param['vid']];
        return $this->store($param);
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
