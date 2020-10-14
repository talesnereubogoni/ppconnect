<?php
/**
 * ImprimeProva Active Record
 * @author  <your-name-here>
 */
class ImprimeProva extends TRecord
{
    const TABLENAME = 'imprime_prova';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('turma_nome');
        parent::addAttribute('disciplina_nome');
        parent::addAttribute('prova_nome');
        parent::addAttribute('cpf');
        parent::addAttribute('data_prova');
        parent::addAttribute('curso_nome');
    }


}
