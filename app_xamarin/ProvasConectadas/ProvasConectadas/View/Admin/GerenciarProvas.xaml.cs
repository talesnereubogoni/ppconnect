using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

using ProvasConectadas.DB;
using Plugin.Toast;
using Plugin.Toast.Abstractions;
using ProvasConectadas.Model;
using System.Collections.ObjectModel;
using Newtonsoft.Json;
using ProvasConectadas.Model.Auxiliar;
using System.IO;
using System.IO.Compression;
using System.Net.Http;
using Plugin.Media.Abstractions;
using Plugin.Media;
using System.Net;

namespace ProvasConectadas.View.Admin
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class GerenciarProvas : ContentPage
    {
        List<DisciplinaProvas> ldp = new List<DisciplinaProvas>();
        public GerenciarProvas()
        {
            InitializeComponent();
            RefreshData();
        }

        private void RefreshData()
        {
            Database db = new Database();
             ldp = db.ListaDiscipliasProvas(DadosAdmin.idcurso);
            if (ldp != null)
                listadp.ItemsSource = ldp;
        }

        private Prova CriaProva(DisciplinaProva d)
        {
            Prova p = new Prova();
            //criar a prova
            try
            {
                string Conteudo = WebService.WebService.CarregaUmaProvaString(d.idDisciplinas.ToString());
                if (Conteudo == null)
                {
                    DisplayAlert("Erro", "Erro para carregar a prova da disciplina!", "OK");
                    return null;
                }
                ObservableCollection<Prova> provas = JsonConvert.DeserializeObject<ObservableCollection<Prova>>(Conteudo);
                if (provas == null || provas[0].idProvaGerada <= 0)
                {
                    DisplayAlert("Erro", "Não foi possivel desserealizar a prova!", "OK");
                    return null;
                }
                p = provas[0];
                p.cpf = "";
                p.datarecebida = DateTime.Now;
                p.disciplina = d.nome;
                p.usada = false;
                p.disciplinas_idDisciplinas = d.idDisciplinas;
                try
                {
                    Database db = new Database();
                    db.Inserir(p);
                }
                catch (Exception)
                {
                    DisplayAlert("Erro", "Não foi possível inserir a prova no banco de dados", "OK");
                    return null;
                }
            }
            catch (Exception)
            {
                DisplayAlert("Erro", "Erro ao carregar prova", "OK");
                return null;
            }
            return p;
        }

        private Questao CriarUmaQuestao(QuestoesDaProvaGerada qp)
        {
            string questao = WebService.WebService.BuscarQuestao(qp.questoes_idQuestoes);
            if (questao == null)
            {
                DisplayAlert("Erro", "Uma questão "+qp.idquestoes_da_prova_gerada.ToString(), "OK");
                return null;
            }
            try
            {
                Questao q = JsonConvert.DeserializeObject<Questao>(questao);
                if (q == null || String.IsNullOrEmpty(q.enunciado))
                {
                    DisplayAlert("Erro", "Erro ao desserealizar a questão! " + q.numero, ToString() + " " + q.idquestoes_da_prova_gerada.ToString(), "OK");
                    return null;
                }

                q.idprova = qp.provagerada_idProvaGerada;
                q.numero = qp.numero_da_questao;
                q.idquestoes_da_prova_gerada = qp.idquestoes_da_prova_gerada;
                AuxBlob bl = WebService.WebService.BuscarImagemDaQuestao(qp.questoes_idQuestoes);
                if (bl != null)
                {
                    q.urlimagem = bl.blob;
                    //DisplayAlert("Atenção", "Questão com imagem", "OK");
                }

                bl = WebService.WebService.BuscarVideoDaQuestao(qp.questoes_idQuestoes);
                if (bl != null)
                {
                    q.urlvideo = bl.blob;
                    //DisplayAlert("Atenção", "Questão com vídeo", "OK");
                }

                bl = WebService.WebService.BuscarAudioDaQuestao(qp.questoes_idQuestoes);
                if (bl != null)
                {
                    q.urlaudio = bl.blob;
                    //DisplayAlert("Atenção", "Questão com audio", "OK");
                }

                try
                {
                    Database db = new Database();
                    db.Inserir(q);
                }
                catch (Exception)
                {
                    DisplayAlert("Erro", "Não foi possível salvar a questão", "OK");
                    return null;
                }
                return q;
            } catch (Exception)
            {
                //DisplayAlert("Teste", questao, "OK");
                return null;
            }
        }

        private Alternativas CriaUmaAlternativa(int id, String Letra, int idquestao)
        {
            Alternativas a = new Alternativas();
            string Conteudo  = WebService.WebService.BuscarAlternativa(id);
            //DisplayAlert("Teste", Conteudo, "ok");
            if (Conteudo != null)
            {
               ObservableCollection<Alternativas> alt = JsonConvert.DeserializeObject<ObservableCollection<Alternativas>>(Conteudo);
               if(alt !=null && alt[0].id > 0)
               {
                    a = alt.First();
//                    if (a.texto == null)
//                        a.texto = "";
                    a.letra = Letra;
                    a.idquestao = idquestao;
                    /*
                    AuxBlob bla1 = null;// = WebService.WebService.BuscarImagemDaAlternativa(id);
                    DisplayAlert("FDP", Conteudo + " " + id + " " + idquestao, "OK");
                    // copiando para ca o download das imagens
                    string EnderecoUrl = DadosAdmin.conexao + "ppconnrest/alternativa/imagem/{0}";
                    DisplayAlert("RUL", EnderecoUrl, "OK");
                    string NovoEnderecoUrl = string.Format(EnderecoUrl, id);
                    string Conteudo1 = "branco";
                    try
                    {
                        WebClient wc = new WebClient();
                        Conteudo1 = wc.DownloadString(NovoEnderecoUrl);
                        AuxBlob blob = JsonConvert.DeserializeObject<AuxBlob>(Conteudo1);
                        if (blob != null)
                        {
                            DisplayAlert("BLOBNAONULL", Conteudo1, "OK");
                            bla1 = blob;
                        }                            
                        else
                            DisplayAlert("BLOBNULL", Conteudo1, "OK");

                    }
                    catch
                    {
                        DisplayAlert("DeuPau", Conteudo1 , "OK");
                    }
                    //

                    

                    if (bla1 != null) // não está entrando
                    {
                        //a.urlimagem = bla.blob;
                        DisplayAlert("Atenção", "Alternativa com imagem "+bla1.blob.ToString(), "OK");
                    }
                    /* 
                    AuxBlob bla = WebService.WebService.BuscarVideoDaAlternativa(id);
                    if (bla != null)
                    {
                        a.urlvideo = bla.blob;
                        DisplayAlert("Atenção", "Alternativa com vídeo", "OK");
                    }
                    
                    bla = WebService.WebService.BuscarAudioDaAlternativa(id);
                    if (bla != null)
                    {
                        a.urlaudio = bla.blob;
                        DisplayAlert("Atenção", "Alternativa com audio", "OK");
                    }
                    */

                }
                return a;
            }
            return null;
        }

        private bool CriaAlternativas(QuestoesDaProvaGerada qp)
        {
            //DisplayAlert("CriarAlternativas", "Tem Alternativas", "OK");
            Database db = new Database();            
            // selecionar as alternativas da questão
            if (qp.a > 0 && qp.a > 0) // carregar a alternativa A
            {
                Alternativas alternativa = CriaUmaAlternativa(qp.a, "A", qp.idquestoes_da_prova_gerada);
                if (alternativa != null)
                    try
                    {
                        db.Inserir(alternativa);
                    }
                    catch (Exception)
                    {
                        DisplayAlert("Erro", "Não foi possível salvara Alternativa A questão " + qp.idquestoes_da_prova_gerada.ToString(), "OK");
                    }                        
            }

            if (qp.b > 0 && qp.b > 0) // carregar a alternativa B
            {
                Alternativas alternativa = CriaUmaAlternativa(qp.b, "B", qp.idquestoes_da_prova_gerada);
                if (alternativa != null)
                    if (alternativa != null)
                        try
                        {
                            db.Inserir(alternativa);
                        }
                        catch (Exception)
                        {
                            DisplayAlert("Erro", "Não foi possível salvara Alternativa B questão " + qp.idquestoes_da_prova_gerada.ToString(), "OK");
                        }
            }

            if (qp.c > 0 && qp.c > 0) // carregar a alternativa C
            {
                Alternativas alternativa = CriaUmaAlternativa(qp.c, "C", qp.idquestoes_da_prova_gerada);
                if (alternativa != null)
                    if (alternativa != null)
                        try
                        {
                            db.Inserir(alternativa);
                        }
                        catch (Exception)
                        {
                            DisplayAlert("Erro", "Não foi possível salvara Alternativa C questão " + qp.idquestoes_da_prova_gerada.ToString(), "OK");
                        }
            }

            if (qp.d>0 && qp.d > 0) // carregar a alternativa D
            {
                Alternativas alternativa = CriaUmaAlternativa(qp.d, "D", qp.idquestoes_da_prova_gerada);
                if (alternativa != null)
                    if (alternativa != null)
                        try
                        {
                            db.Inserir(alternativa);
                        }
                        catch (Exception)
                        {
                            DisplayAlert("Erro", "Não foi possível salvara Alternativa D questão " + qp.idquestoes_da_prova_gerada.ToString(), "OK");
                        }
            }

            if (qp.e>0 && qp.e > 0) // carregar a alternativa E
            {
                Alternativas alternativa = CriaUmaAlternativa(qp.e, "E", qp.idquestoes_da_prova_gerada);
                if (alternativa != null)
                    if (alternativa != null)
                        try
                        {
                            db.Inserir(alternativa);
                        }
                        catch (Exception)
                        {
                            DisplayAlert("Erro", "Não foi possível salvara Alternativa E questão " + qp.idquestoes_da_prova_gerada.ToString(), "OK");
                        }
            }
            return true;
        }

        private bool CriarQuestoes(Prova p)
        {
            String Conteudo = WebService.WebService.BuscarQuestoesProvaGerada(p.idProvaGerada);
            if (!String.IsNullOrEmpty(Conteudo))
            {
                ObservableCollection<QuestoesDaProvaGerada> listadequestoes = JsonConvert.DeserializeObject<ObservableCollection<QuestoesDaProvaGerada>>(Conteudo);
                if (listadequestoes != null)
                {
                    foreach (QuestoesDaProvaGerada qp in listadequestoes)
                    {
                        Questao q = CriarUmaQuestao(qp);
                        if (q==null) // erro ao criar uma questão
                        {
                            DisplayAlert("Erro", "Erro o criar uma questão da lista de questões "+qp.idquestoes_da_prova_gerada.ToString(), "OK");
                        }
                        else
                        {
                            if(q.tipodaquestao == DadosAdmin.QUESTAOFECHADA)//múltipla escolha
                            {
                                bool ca = CriaAlternativas(qp);
                            }
                        }                            
                    }
                }
                else
                {
                    DisplayAlert("Erro", "Lista de questões nula", "OK");
                }
            }
            else
            {
                DisplayAlert("Erro", "Conteúdo da Questão em Branco", "OK");
            }
            return true;
        }

        private void BaixarProva(string iddisciplina)
        {
            Prova p = new Prova();
            Database db = new Database();
            DisciplinaProva d = db.ConsultaDisciplina(Convert.ToInt32(iddisciplina));
            if (d != null) // existe uma disciplina
            {
                p = CriaProva(d);
                if(p==null)
                {
                    DisplayAlert("Erro", "Não carregou a prova", "OK");
                    return;
                }
                // carregar as questões                
                bool listaquestoes = CriarQuestoes(p);
                if(listaquestoes)
                    DisplayAlert("Sucesso", "Prova carregada com sucesso!", "OK");
                else
                    DisplayAlert("Sucesso", "Erro ao carregar as questões da prova!", "OK");
            }
            else
            {
                DisplayAlert("Erro", "Não existe disciplina", "OK");
            }
            //CrossToastPopUp.Current.ShowToastSuccess("Alternativas prova carregada", toastLength);
        }


        private async void Bt_atualizar_Clicked(object sender, EventArgs e)
        {
            ImageButton button = (ImageButton)sender;
            string iddisciplina = button.CommandParameter.ToString();
            bool resp;
            // verifica o status da prova
            int i = 0;
            for (i = 0; i < ldp.Count; i++)
                if (ldp[i].idDisciplina == Convert.ToInt32(iddisciplina))
                    break;
            if (ldp[i].provausada.Equals("Sim")) // envia a prova
            {
                resp = await DisplayAlert("Envio de Prova", "Ao concluir o envio os dados da prova serão apagados deste dispositivo. Confirma o envio da prova para o servidor?", "Sim", "Não");
                if (resp)
                {
                    Database db = new Database();
                    //pegar a prova da base de dados local
                    Prova p = db.SelecionaProvaDaDisciplina(Convert.ToInt32(iddisciplina));
                //    DisplayAlert("ok", iddisciplina + p.idProvaGerada.ToString(), "ok"); ;
                    bool r = await WebService.WebService.AtualizarProvaRealizada(p);
                    
                    if (r)
                    {
                        List<Questao> lq = db.SelecionaQuestoes(p);
                        foreach (Questao q in lq)
                        {
                            DadosDaQuestao dq = new DadosDaQuestao();
                            dq.id = q.idquestoes_da_prova_gerada;
                            dq.respostaaberta = q.respostaaberta;
                            dq.respostafechada = q.respostafechada;//db.AlternativaSelecionada(q);
                            dq.respostaletra = q.respostafechadaletra;
                            bool r1 = await WebService.WebService.AtualizarQuestaoRealizada(dq);
                            if (!r1)
                            {
                                await DisplayAlert("Erro", "Ocorreu algum erro na transmissão da questao " + dq.id.ToString(), "ok") ;
                            } else
                            {
                                if (q.tipodaquestao == DadosAdmin.QUESTAOABERTA)
                                { // aberta                                    
                                    //apaga os arquivos de resposta
                                    bool r2 = await WebService.WebService.EnviarRespostasMidia(q);
                                    if (r2)
                                    {
                                        if(!String.IsNullOrEmpty(q.urlrespostaaudio))
                                            File.Delete(q.urlrespostaaudio);
                                        if (!String.IsNullOrEmpty(q.urlrespostavideo))
                                            File.Delete(q.urlrespostavideo);
                                        if (!String.IsNullOrEmpty(q.urlrespostaimagem))
                                            File.Delete(q.urlrespostaimagem);
                                    }
                                }
                                await DisplayAlert("Apagando", "Questão "+q.idquestoes_da_prova_gerada, "OK");
                                db.Deletar(q);    
                            }
                        }
                        await DisplayAlert("Apagando", "Prova " + p.idProvaGerada, "OK");
                        //db.Deletar(p);
                    }
                    else
                    {
                        await DisplayAlert("Apagando", "Erro 1", "OK");
                    }
                         
                  //  await DisplayAlert("Para implementar", "Fazer a função de envio da prova "+r.ToString(), "ok");
                }
            }
            else
            {
                if (string.IsNullOrEmpty(ldp[i].data.Trim())) // recebe a prova
                {
                    BaixarProva(iddisciplina);
                    RefreshData();
                }
                else // apaga a prova
                {
                    resp = await DisplayAlert("Exclusão de Prova", "Confirma a exclusão da prova?", "Sim", "Não");
                    if (resp)
                    {
                        Database db = new Database();
                        //pegar a prova da base de dados local
                        Prova p = db.SelecionaProvaDaDisciplina(Convert.ToInt32(iddisciplina));
                        if (p != null)
                        {
                            var lq = db.SelecionaQuestoes(p);
                            foreach (Questao qp in lq)
                            {
                                db.Deletar(qp);
                            }
                            db.Deletar(p);
                            RefreshData();
                        }
                    }
                }
            }            
        }
        
        private void Bt_carregarDisciplinas_Clicked_1(object sender, EventArgs e)
        {
            try
            {
                Database db = new Database();
                int cursotutor = DadosAdmin.idcurso;
                //DisplayAlert("servidor", DadosAdmin.conexao, "ok");
                string dados = WebService.WebService.BuscarDisciplinasComProva(cursotutor);
                ObservableCollection<DisciplinaProva> dl = JsonConvert.DeserializeObject<ObservableCollection<DisciplinaProva>>(dados);
                if (dl != null)
                {
                    int i = 0;
                    foreach (DisciplinaProva d in dl)
                    {

                        if (db.Inserir(d))
                            i++;
                    }
                    DisplayAlert("Sucesso", "Foram importadas " + i.ToString() + " disciplinas", "OK");
                    RefreshData();
                }
                else
                    DisplayAlert("Aviso", "Não existem disciplpinas para serem improtadas", "OK");
            }
            catch (Exception)
            {
                DisplayAlert("Erro", "Problemas na comunicação com o servidor", "OK");
            }
        }
        public static byte[] Compress(byte[] data)
        {
            // Fonte: http://stackoverflow.com/a/271264/194717
            using (var compressedStream = new MemoryStream())
            using (var zipStream = new GZipStream(compressedStream, CompressionMode.Compress))
            {
                zipStream.Write(data, 0, data.Length);
                zipStream.Close();
                return compressedStream.ToArray();
            }
        }
    }

}