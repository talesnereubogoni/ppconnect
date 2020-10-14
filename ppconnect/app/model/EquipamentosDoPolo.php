<?php
/**
 * EquipamentosDoPolo Active Record
 * @author  <your-name-here>
 */
class EquipamentosDoPolo extends TRecord
{
    const TABLENAME = 'equipamentos_do_polo';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $tipos_de_equipamentos;
    private $polos;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('polos_id');
        parent::addAttribute('equipamentos_id');
        parent::addAttribute('codigo');
        parent::addAttribute('descricao');
    }

    
    /**
     * Method set_tipos_de_equipamentos
     * Sample of usage: $equipamentos_do_polo->tipos_de_equipamentos = $object;
     * @param $object Instance of TiposDeEquipamentos
     */
    public function set_tipos_de_equipamentos(TiposDeEquipamentos $object)
    {
        $this->tipos_de_equipamentos = $object;
        $this->tipos_de_equipamentos_id = $object->id;
    }
    
    /**
     * Method get_tipos_de_equipamentos
     * Sample of usage: $equipamentos_do_polo->tipos_de_equipamentos->attribute;
     * @returns TiposDeEquipamentos instance
     */
    public function get_tipos_de_equipamentos()
    {
        // loads the associated object
        if (empty($this->tipos_de_equipamentos))
            $this->tipos_de_equipamentos = new TiposDeEquipamentos($this->equipamentos_id);
    
        // returns the associated object
        return $this->tipos_de_equipamentos;
    }
    
    
    /**
     * Method set_polos
     * Sample of usage: $equipamentos_do_polo->polos = $object;
     * @param $object Instance of Polos
     */
    public function set_polos(Polos $object)
    {
        $this->polos = $object;
        $this->polos_id = $object->id;
    }
    
    /**
     * Method get_polos
     * Sample of usage: $equipamentos_do_polo->polos->attribute;
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
