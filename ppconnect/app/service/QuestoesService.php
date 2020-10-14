<?php
/**
 * Questoes REST service
 */
class QuestoesService extends AdiantiRecordService
{
    const DATABASE      = 'ppconnect';
    const ACTIVE_RECORD = 'Questoes';
    
    public function loadMidia($param){
          // param é o endereço da imagem
          $data = $param['url_midia'];//$lista[0]->img;
          $imagem = file_get_contents($data);
          $cript = base64_encode($imagem);
          $src = mime_content_type($param['url_midia']).';base64, '.$cript;
          //$arr = array($src);          
          $return []= $src;
        
        TTransaction::close();
        return $return;
        
          
          
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
