<?php
/**
 * ProvasGeradas REST service
 */
class ProvasGeradasService extends AdiantiRecordService
{
    const DATABASE      = 'ppconnect';
    const ACTIVE_RECORD = 'ProvasGeradas';
    
    
    public function baixarProva($param)
    {
        $database     = static::DATABASE;
        $activeRecord = static::ACTIVE_RECORD;
        
        TTransaction::open($database);        
        $repositorio = new TRepository('EquipamentosDoPolo'); 
        $criterio = new TCriteria;
        $criterio->add(new TFilter ('codigo', '=', $param['codigo']));                
        $equipamentos = $repositorio->load($criterio);
        TTransaction::close();
        $polos_id = 0;
        if ($equipamentos)
        {
            foreach ($equipamentos as $equipamento){
                $polos_id = $equipamento->polos_id; // pega o id do polo para onde a prova será mandada
            }
        } else {
            return null;
        }
        // validou o equipamento que recebe os dados
        
        $param['filters'] = [['provas_id', '=', $param['provas_id']], ['usada', '=', 'N'] ];
        $param['limit']=1; // apenas o primeiro
        return $this->loadAll($param);       
    }

    /**
     * baixarProvas($param)
     *
     * Retorna a lista com a qtd de provas
     * @ return lista de provas
     * 
     * @param $param['provas_id'] id da prova, 
     *              ['qtd'] quantidade de provas,
     *              ['polos_id'] polo que pediu a prova,
     *              ['codigo'] código de acesso do polo,
     *              ['palavra_passe'] senha de acesso do polo
     */
    public function baixarProvas($param)
    {
        $database     = static::DATABASE;
        $activeRecord = static::ACTIVE_RECORD;
        
        TTransaction::open($database);        
        $repositorio = new TRepository('EquipamentosDoPolo'); 
        $criterio = new TCriteria;
        $criterio->add(new TFilter ('codigo', '=', $param['codigo']));                
        $equipamentos = $repositorio->load($criterio);
        TTransaction::close();
        $polos_id = 0;
        if ($equipamentos)
        {
            foreach ($equipamentos as $equipamento){
                $polos_id = $equipamento->polos_id; // pega o id do polo para onde a prova será mandada
            }
        } else {
            return null;
        }
        // validou o equipamento que recebe os dados
        
        $param['filters'] = [['provas_id', '=', $param['provas_id']], ['usada', '=', 'N'] ];
        $param['limit']=$param['qtd']; // apenas o primeiro
        return $this->loadAll($param);       
    }
    
     /**
     * provasDisponiveis($param)
     *
     * Retorna a quantidade de provas disponíveis
     * @ return qtd
     * 
     * @param $param['id'] id da prova
     */
     public function provasDisponiveis($param)
     {        
        TTransaction::open(static::DATABASE); // open a transaction
        $n = ProvasGeradas::where('provas_id','=',$param['id'])
                            ->where('usada','=', "N")->count();
        TTransaction::close();
        $data [] = ['qtd'=> $n];//$object->toArray( $attributes )
        return $data;        
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
