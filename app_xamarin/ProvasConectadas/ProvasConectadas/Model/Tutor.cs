using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    [Table("Tutores")]
    public class Tutor
    {
        public int idUsuario { get; set; }
        public string nome { get; set; }
        public string email { get; set; }
        public string telefone { get; set; }
        public string senha { get; set; }
        public int idCurso { get; set; }
        public int tipousuario { set; get; }


    }
}
