<?php
/**
 * QuestoesDasProvasGeradas Active Record
 * @author  <your-name-here>
 */
class QuestoesDasProvasGeradas extends TRecord
{
    const TABLENAME = 'questoes_das_provas_geradas';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $questoes;
    private $provas_geradas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('questoes_id');
        parent::addAttribute('provas_geradas_id');
        parent::addAttribute('numero_da_questao');
        parent::addAttribute('resposta_texto');
        parent::addAttribute('resposta_alternativa_id');
        parent::addAttribute('resposta_vf');
        parent::addAttribute('nota');
        parent::addAttribute('video');
        parent::addAttribute('audio');
        parent::addAttribute('imagem');
        parent::addAttribute('resposta_transcricao');
        parent::addAttribute('a_alternativas_id');
        parent::addAttribute('b_alternativas_id');
        parent::addAttribute('c_alternativas_id');
        parent::addAttribute('d_alternativas_id');
        parent::addAttribute('e_alternativas_id');
        parent::addAttribute('resposta_letra');
        parent::addAttribute('corrigida');
        parent::addAttribute('enviada');
        parent::addAttribute('correcao_subjetiva');
    }

    
    /**
     * Method set_questoes
     * Sample of usage: $questoes_das_provas_geradas->questoes = $object;
     * @param $object Instance of Questoes
     */
    public function set_questoes(Questoes $object)
    {
        $this->questoes = $object;
        $this->questoes_id = $object->id;
    }
    
    /**
     * Method get_questoes
     * Sample of usage: $questoes_das_provas_geradas->questoes->attribute;
     * @returns Questoes instance
     */
    public function get_questoes()
    {
        // loads the associated object
        if (empty($this->questoes))
            $this->questoes = new Questoes($this->questoes_id);
    
        // returns the associated object
        return $this->questoes;
    }
    
    
    /**
     * Method set_provas_geradas
     * Sample of usage: $questoes_das_provas_geradas->provas_geradas = $object;
     * @param $object Instance of ProvasGeradas
     */
    public function set_provas_geradas(ProvasGeradas $object)
    {
        $this->provas_geradas = $object;
        $this->provas_geradas_id = $object->id;
    }
    
    /**
     * Method get_provas_geradas
     * Sample of usage: $questoes_das_provas_geradas->provas_geradas->attribute;
     * @returns ProvasGeradas instance
     */
    public function get_provas_geradas()
    {
        // loads the associated object
        if (empty($this->provas_geradas))
            $this->provas_geradas = new ProvasGeradas($this->provas_geradas_id);
    
        // returns the associated object
        return $this->provas_geradas;
    }
    


}
