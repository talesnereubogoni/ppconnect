using System;
using System.Collections.Generic;
using System.Text;
using ProvasConectadas.Model;
using Newtonsoft.Json;
using System.Net;
using System.Collections.ObjectModel;
using System.Net.Http;
using System.Threading.Tasks;
using ProvasConectadas.Model.Auxiliar;
using ProvasConectadas.View.Admin;
using System.IO;

namespace ProvasConectadas.WebService
{
    public class WebService
    {
        // ALGORITMOS PRONTOS
        /* retorna uma string com a lista de cursos do polo ao qual a MAC do dispositivo
         * está associada
         */
        public static String  BuscarCursos(string mac)
        {
            try
            {
                string EnderecoUrl = DadosAdmin.conexao+"ppconnrest/cursos/polo/mac/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, mac);
                WebClient wc = new WebClient();
                string Conteudo = wc.DownloadString(NovoEnderecoUrl);
                return Conteudo;
            }
            catch (Exception) { return null; }
        }

        /* retorna uma string com a lista de tutores dos cursos do polo ao qual a MAC 
         * do dispositivo está associada
         */
        public static string BuscarTutores(string mac)
        {
            try
            {
                string EnderecoUrl = DadosAdmin.conexao+"ppconnrest/tutores/polo/mac/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, mac);
                WebClient wc = new WebClient();
                string Conteudo = wc.DownloadString(NovoEnderecoUrl);
                return Conteudo;
            }
            catch (Exception)
            {
                return null;
            }
        }

        /* retorna uma string com os dados da prova de uma disciplina pelo idDisciplina
         */
        public static string CarregaUmaProvaString(string idDisciplina)
        {
            try
            {
                string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/provasgeradas/carrega/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, idDisciplina);
                WebClient wc = new WebClient();
                return wc.DownloadString(NovoEnderecoUrl);
            }
            catch (Exception)
            {
                return null;
            }
        }

        /* retorna uma string com as questões de uma prova gerada, com base no id da prova gerada
         */
        public static string BuscarQuestoesProvaGerada(int id)
        {
            try
            {
                string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/provasgeradas/questoes/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
                WebClient wc = new WebClient();
                return wc.DownloadString(NovoEnderecoUrl);                
            }
            catch (Exception) {
                return null;
            }
        }

        /* retorna uma string os dados de uma questão
         */
        public static string BuscarQuestao(int questao)
        {
            try
            {
                string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/questao/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, questao);
                WebClient wc = new WebClient();
                return wc.DownloadString(NovoEnderecoUrl);
            }
            catch (Exception)
            {
                return null;
            }
        }

        /* Método auxiliar para trabalhar com blobs
         */
        private static AuxBlob BuscarBlob(string local)
        {
            try
            {
                WebClient wc = new WebClient();
                string Conteudo = wc.DownloadString(local);
                AuxBlob blob = JsonConvert.DeserializeObject<AuxBlob>(Conteudo);
                if(blob.blob.Length>0)
                    return blob;
                return null;
            }
            catch
            {
                return null;
            }
        }

        /* retorna um blob com a imagem associada a uma questão
         */
        public static AuxBlob BuscarImagemDaQuestao(int id)
        {
            string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/questao/imagem/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
            return BuscarBlob(NovoEnderecoUrl);
        }

        /* retorna um blob com um vídeo associado a uma questão
         */
        public static AuxBlob BuscarVideoDaQuestao(int id)
        {
            string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/questao/video/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
            return BuscarBlob(NovoEnderecoUrl);
        }

        /* retorna um blob com um audio associado a uma questão
         */
        public static AuxBlob BuscarAudioDaQuestao(int id)
        {
            string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/questao/audio/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
            return BuscarBlob(NovoEnderecoUrl);
        }


        /* retorna uma string com os dados de uma alternativa
         */
        public static string BuscarAlternativa(int id)
        {
            try
            {
                string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/alternativa/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
                WebClient wc = new WebClient();
                return wc.DownloadString(NovoEnderecoUrl);
            }
            catch (Exception)
            {
                return null;
            }
        }

        /* retorna uma string com as disciplinas que possuem provas em aberto
         */
        public static string BuscarDisciplinasComProva(int idcurso)
        {
            //List<Disciplina> lista = new List<Disciplina>();
            try
            {
                string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/provasgeradas/disciplinas/curso/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, idcurso);
                WebClient wc = new WebClient();
                string Conteudo = wc.DownloadString(NovoEnderecoUrl);
                return Conteudo;
            }
            catch (Exception)
            {
                return null;
            }
        }

        //atualiza os dados de uma questão com a resposta do aluno
        public async static Task<bool> AtualizarQuestaoRealizada(DadosDaQuestao dq)
        {
            string NovoEnderecoUrl = DadosAdmin.conexao + "ppconnrest/questaorealizada";
            HttpClient wc = new HttpClient();
            var json = JsonConvert.SerializeObject(dq);
            var content = new StringContent(json, Encoding.UTF8, "application/json");
            HttpResponseMessage response = await wc.PutAsync(NovoEnderecoUrl, content);
            if (response.IsSuccessStatusCode)
            {
                return true;
            }
            return false;
        }

        public async static Task<bool> AtualizarQuestaoRealizadaImagem(AuxBlobString dq)
        {
            string NovoEnderecoUrl = DadosAdmin.conexao + "ppconnrest/questaorealizada/imagem";
            HttpClient wc = new HttpClient();
            var json = JsonConvert.SerializeObject(dq);
            var content = new StringContent(json, Encoding.UTF8, "application/json");
            HttpResponseMessage response = await wc.PutAsync(NovoEnderecoUrl, content);
            if (response.IsSuccessStatusCode)
            {
                return true;
            }
            return false;
        }

        public async static Task<bool> AtualizarQuestaoRealizadaImagem(AuxBlob dq)
        {
            string NovoEnderecoUrl = DadosAdmin.conexao + "ppconnrest/questaorealizada/imagem";
            HttpClient wc = new HttpClient();
            var json = JsonConvert.SerializeObject(dq);
            var content = new StringContent(json, Encoding.UTF8, "application/json");
            HttpResponseMessage response = await wc.PutAsync(NovoEnderecoUrl, content);
            if (response.IsSuccessStatusCode)
            {
                return true;
            }
            return false;
        }

        // atualiza as midias da resposta
        public async static Task<bool> EnviarRespostasMidia(Questao q)
        {
            var content = new MultipartFormDataContent();
            if (!String.IsNullOrEmpty(q.urlrespostaimagem))// não está vazia
            {
                FileStream fs = new FileStream(q.urlrespostaimagem, FileMode.Open, FileAccess.Read);
                content.Add(new StreamContent(fs), "\"imagem\"", $"\"{q.urlrespostaimagem}\"");
            }
            if (!String.IsNullOrEmpty(q.urlrespostavideo))// não está vazia
            {
                FileStream fs = new FileStream(q.urlrespostavideo, FileMode.Open, FileAccess.Read);
                content.Add(new StreamContent(fs), "\"video\"", $"\"{q.urlrespostavideo}\"");
            }
            if (!String.IsNullOrEmpty(q.urlrespostaaudio))// não está vazia
            {
                FileStream fs = new FileStream(q.urlrespostaaudio, FileMode.Open, FileAccess.Read);
                content.Add(new StreamContent(fs), "\"audio\"", $"\"{q.urlrespostaaudio}\"");
            }

            var client = new HttpClient();
            var uploadServiceBaseAddress = DadosAdmin.conexao + "/imagens/recebearquivo.php";
            HttpResponseMessage response = await client.PostAsync(uploadServiceBaseAddress, content);
            if (response.IsSuccessStatusCode)
            {
                return true;
            }
            return false;

        }

        //atualiza os dados da prova com os dados do aluno e data da realização
        public async static Task<bool> AtualizarProvaRealizada(Prova p)
        {
            string NovoEnderecoUrl = DadosAdmin.conexao + "/ppconnrest/provarealizada";
            HttpClient wc = new HttpClient();
            var json = JsonConvert.SerializeObject(p);
            var content = new StringContent(json, Encoding.UTF8, "application/json");
            HttpResponseMessage response = await wc.PutAsync(NovoEnderecoUrl, content);
            if (response.IsSuccessStatusCode)
            {
                return true;
            }
            return false;

        }


        // ainda precisam de ajustes



        /*
        //carrega os dados de uma questão com base em seu numero
        public static Questao BuscarQuestao(int questao)
        {
            string EnderecoUrl = "http://www.ppconnect.com.br/ppconnrest/questao/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, questao);
            WebClient wc = new WebClient();
            string Conteudo = wc.DownloadString(NovoEnderecoUrl);
            Questao questaorecebida = JsonConvert.DeserializeObject<Questao>(Conteudo);
            return questaorecebida;
        }
        */
        //carrega uma prova usando prova como parâmetro
        public static Prova CarregaUmaProva(Prova p)
        {
            string EnderecoUrl = "http://www.ppconnect.com.br/ppconnrest/provasgeradas/carrega/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, p.disciplina);
            WebClient wc = new WebClient();
            string Conteudo = wc.DownloadString(NovoEnderecoUrl);
            ObservableCollection<Prova> p1 = JsonConvert.DeserializeObject< ObservableCollection<Prova>>(Conteudo);
            return p1[0];
        }

        //carrega uma prova usando a disciplina como parâmetro
        public static Prova CarregaUmaProva(DisciplinaProva d)
        {
            string EnderecoUrl = "http://www.ppconnect.com.br/ppconnrest/provasgeradas/carrega/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, d.idDisciplinas);
            WebClient wc = new WebClient();
            string Conteudo = wc.DownloadString(NovoEnderecoUrl);
            ObservableCollection<Prova> p1 = JsonConvert.DeserializeObject<ObservableCollection<Prova>>(Conteudo);
            return p1[0];
        }

        


        


        

        public static ObservableCollection<Questao> BuscarQuestoesDaDisciplina(string disciplina)
        {
            try
            {
                string EnderecoUrl = "http://www.ppconnect.com.br/ppconnrest/questoes/{0}";
                string NovoEnderecoUrl = string.Format(EnderecoUrl, disciplina);
                WebClient wc = new WebClient();
                string Conteudo = wc.DownloadString(NovoEnderecoUrl);
                ObservableCollection<Questao> lista = JsonConvert.DeserializeObject<ObservableCollection<Questao>>(Conteudo);
                return lista;
            } catch (Exception) { return null; }
        }

       





        // Busca a imagem de uma alternativa
        public static AuxBlob BuscarImagemDaAlternativa(int id)
        {
            string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/alternativa/imagem/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
            return BuscarBlob(NovoEnderecoUrl);
        }

        // Busca a video de uma alternativa
        public static AuxBlob BuscarVideoDaAlternativa(int id)
        {
            string EnderecoUrl = DadosAdmin.conexao + "/ppconnrest/alternativa/video/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
            return BuscarBlob(NovoEnderecoUrl);
        }

        // Busca a audio de uma alternativa
        public static AuxBlob BuscarAudioDaAlternativa(int id)
        {
            string EnderecoUrl = DadosAdmin.conexao + "/ppconnrest/alternativa/audio/{0}";
            string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
            return BuscarBlob(NovoEnderecoUrl);
        }




        

    }
}
