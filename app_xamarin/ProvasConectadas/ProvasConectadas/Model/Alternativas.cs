using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    [Table("Alternativas")]
    public class Alternativas
    {
        public int id { get; set; }
        public int idquestao { get; set; }
        public string letra { set; get; }
        public string texto { get; set; }
        public byte[] urlimagem { get; set; }
        public byte[] urlvideo { get; set; }
        public byte[] urlaudio { get; set; }
    }
}
