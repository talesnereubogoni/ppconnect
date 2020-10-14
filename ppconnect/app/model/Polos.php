<?php
/**
 * Polos Active Record
 * @author  <your-name-here>
 */
class Polos extends TRecord
{
    const TABLENAME = 'polos';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    private $system_user;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('bairro');
        parent::addAttribute('rua');
        parent::addAttribute('numero');
        parent::addAttribute('telefone');
        parent::addAttribute('cep');
        parent::addAttribute('email');
        parent::addAttribute('whatsapp');
        parent::addAttribute('coordenador');
        parent::addAttribute('responsavel_id');
    }

    
    /**
     * Method set_system_user
     * Sample of usage: $polos->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->system_user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $polos->system_user->attribute;
     * @returns SystemUser instance
     */
    public function get_system_user()
    {
        // loads the associated object
        if (empty($this->system_user))
            $this->system_user = new SystemUser($this->system_user_id);
    
        // returns the associated object
        return $this->system_user;
    }
    
    
    


}
