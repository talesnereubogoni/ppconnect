<?php
/**
 * ProvasFeitas Active Record
 * @author  <your-name-here>
 */
class ProvasFeitas extends TRecord
{
    const TABLENAME = 'provas_feitas';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    private $aluno;    
    private $provas_geradas;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('cpf');
        parent::addAttribute('provas_geradas_id');
        parent::addAttribute('inicio');
        parent::addAttribute('fim');
    }

    
    /**
     * Method set_provas_geradas
     * Sample of usage: $provas_feitas->provas_geradas = $object;
     * @param $object Instance of ProvasGeradas
     */
    public function set_provas_geradas(ProvasGeradas $object)
    {
        $this->provas_geradas = $object;
        $this->provas_geradas_id = $object->id;
    }
    
    /**
     * Method get_provas_geradas
     * Sample of usage: $provas_feitas->provas_geradas->attribute;
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
    
    /**
     * Method set_provas_geradas
     * Sample of usage: $provas_feitas->provas_geradas = $object;
     * @param $object Instance of ProvasGeradas
     */
    public function set_aluno(Aluno $object)
    {
        $this->aluno = $object;
        $this->cpf = $object->cpf;
    }
    
    /**
     * Method get_provas_geradas
     * Sample of usage: $provas_feitas->provas_geradas->attribute;
     * @returns ProvasGeradas instance
     */
    public function get_aluno()
    {
        // loads the associated object
        if (empty($this->aluno))
            $this->aluno = Alunos::where('cpf','=',$this->cpf)->load();
            if($this->aluno)
                $this->aluno = $this->aluno[0];    
        // returns the associated object
        return $this->aluno;
    }
    


}
