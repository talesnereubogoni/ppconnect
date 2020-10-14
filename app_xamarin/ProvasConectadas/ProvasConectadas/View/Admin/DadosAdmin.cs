using System;
using System.Collections.Generic;
using System.Text;

namespace ProvasConectadas.View.Admin
{
    static class DadosAdmin
    {
        public static string usuario { get; set; }
        public static int tipousuario { get; set; }
        public static int id { get; set; }
        public static string mac { get; set; }
        public static string conexao { get; set; }
        public static int idcurso { get; set; }

        public static int TUTORES = 3;
        public static int ALUNO = 2;
        public static int QUESTAOFECHADA = 1;
        public static int QUESTAOABERTA = 4;
    }
}
