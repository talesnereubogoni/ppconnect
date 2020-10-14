using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    [Table("Configuracoes")]
    public class Configuracoes
    {
        public string ip { set; get; }
        public string url { set; get; }
        public string chave { set; get; }
        public string senha { set; get; }
        public string mac { set; get; }


    }
}
