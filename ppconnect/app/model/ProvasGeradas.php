<?php
/**
 * ProvasGeradas Active Record
 * @author  <your-name-here>
 */
class ProvasGeradas extends TRecord
{
    const TABLENAME = 'provas_geradas';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $provas;
    private $polos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('provas_id');
        parent::addAttribute('polos_id');
        parent::addAttribute('numero_da_prova');
        parent::addAttribute('data_criada');
        parent::addAttribute('data_enviada');
        parent::addAttribute('data_devolvida');
        parent::addAttribute('cpf_aluno');
        parent::addAttribute('usada');
        parent::addAttribute('inicio');
        parent::addAttribute('fim');
    }

    
    /**
     * Method set_provas
     * Sample of usage: $provas_geradas->provas = $object;
     * @param $object Instance of Provas
     */
    public function set_provas(Provas $object)
    {
        $this->provas = $object;
        $this->provas_id = $object->id;
    }
    
    /**
     * Method get_provas
     * Sample of usage: $provas_geradas->provas->attribute;
     * @returns Provas instance
     */
    public function get_provas()
    {
        // loads the associated object
        if (empty($this->provas))
            $this->provas = new Provas($this->provas_id);
    
        // returns the associated object
        return $this->provas;
    }
    
    
    /**
     * Method set_polos
     * Sample of usage: $provas_geradas->polos = $object;
     * @param $object Instance of Polos
     */
    public function set_polos(Polos $object)
    {
        $this->polos = $object;
        $this->polos_id = $object->id;
    }
    
    /**
     * Method get_polos
     * Sample of usage: $provas_geradas->polos->attribute;
     * @returns Polos instance
     */
    public function get_polos()
    {
        // loads the associated object
        if (empty($this->polos))
            $this->polos = new Polos($this->polos_id);
    
        // returns the associated object
        return $this->polos;
    }
    


}
