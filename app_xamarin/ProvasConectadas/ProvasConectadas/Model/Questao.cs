using System;
using System.Collections.Generic;
using System.Text;
using SQLite;

namespace ProvasConectadas.Model
{
    [Table("Questoes")]
    public class Questao
    {
        [PrimaryKey]
        public int id { get; set; }
        public int idprova { set; get; }
        public int numero { set; get; }
        public int tipodaquestao { get; set; } // 0-multipla escolha, 1-aberta
        public string enunciado { get; set; }
        public byte[] urlimagem { get; set; }
        public byte[] urlvideo { get; set; }
        public byte[] urlaudio { get; set; }
        public string respostaaberta { get; set; }
        public int respostafechada { get; set; }
        public string respostafechadaletra { get; set; }
        public byte[] respostavideo { get; set; }
        public byte[] respostaaudio { get; set; }
        public byte[] respostaimagem { get; set; }
        public string urlrespostavideo { get; set; }
        public string urlrespostaaudio { get; set; }
        public string urlrespostaimagem { get; set; }
        public long temporesposta { get; set; }
        public int idquestoes_da_prova_gerada { set; get; }
        public string resposta_view { set; get; }
        public string color { set; get; }

    }
}
