using Plugin.AudioRecorder;
using Plugin.Media;
using ProvasConectadas.DB;
using ProvasConectadas.Model;
using ProvasConectadas.Model.Auxiliar;
using ProvasConectadas.View.Admin;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Xamarin.Essentials;
using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class EntradaAluno : ContentPage
    {

        AudioRecorderService gravador;
        AudioPlayer reprodutor;

        public Aluno aluno { set;  get; }

        List<Prova> listaprovas = new List<Prova>();
        List<Curso> listacursos = new List<Curso>();
        List<DisciplinaProvas> listadisciplinas = new List<DisciplinaProvas>();

        public EntradaAluno()
        {
            InitializeComponent();
            Database db = new Database();
            listaprovas = db.SelecionarProvasAbertas();
            if (listaprovas != null)
            {
                foreach (Prova p in listaprovas)
                {
                    DisciplinaProva d = db.ConsultaDisciplina(p.disciplinas_idDisciplinas);
                    if (d != null)
                    {
                        Curso c = db.ConsultaCurso(d.curso_id);
                        if (c != null)
                        {
                            bool tem = false;
                            for (int i = 0; i < listacursos.Count; i++)
                                if (c.idCurso == listacursos[i].idCurso)
                                    tem = true;
                            if (!tem)
                            {
                                listacursos.Add(c);
                                Pk_cursos.Items.Add(c.nome);

                            }
                        }
                    }
                }
            }


            //lista = db.SelecionarCursos();
            //foreach (Curso c in lista)
            //{
            //    Pk_cursos.Items.Add(c.nome);
            //}
            /*
            gravador = new AudioRecorderService
            {
                StopRecordingAfterTimeout = true,
                TotalAudioTimeout = TimeSpan.FromSeconds(15),
                AudioSilenceTimeout = TimeSpan.FromSeconds(2)
            };
            reprodutor = new AudioPlayer();
            reprodutor.FinishedPlaying += Finaliza_Reproducao;
            */
            aluno = new Aluno();
        }

        private async void Bt_confirma_Clicked(object sender, EventArgs e)
        {
            if (string.IsNullOrEmpty(cpf.Text) || string.IsNullOrEmpty(nome.Text) ||
                string.IsNullOrEmpty(email.Text))
            {
                await DisplayAlert("Erro", "Todos os dados precisam ser preenchidos para continuar", "OK");
            } else
            {
                var location = await Geolocation.GetLastKnownLocationAsync();
                aluno.cpf = cpf.Text;
                aluno.nome = nome.Text;
                aluno.email = email.Text;
                //falta pegar a imagem e o audio do aluno e converver em blob
                aluno.nometutor = DadosAdmin.usuario;
                aluno.latitude = (float)location.Latitude;
                aluno.longitude = (float)location.Longitude;
                DadosAdmin.usuario = aluno.cpf;
                DadosAdmin.tipousuario = DadosAdmin.ALUNO;
                Database db = new Database();
                db.Inserir(aluno);
                Prova p = db.SelecionarProva(listadisciplinas[Pk_provas.SelectedIndex].idProva);
                if(p!=null)
                {
                    //await DisplayAlert("TesteProva", "Tem prova", "ok");
                    await Navigation.PushAsync(new IniciarProva(p));
                }
                else await DisplayAlert("ok", "Sem Prova "+ listadisciplinas[Pk_provas.SelectedIndex].idProva, "ok");
                
            }
        }

        private byte[] ConvertImageToByteArray(string imagepath)
        {
            byte[] imagebyte = null;
            FileStream fs = new FileStream(imagepath, FileMode.Open, FileAccess.Read);
            using (BinaryReader reader = new BinaryReader(fs))
            {
                imagebyte = new byte[reader.BaseStream.Length];
                for(int i=0; i< reader.BaseStream.Length; i++)
                {
                    imagebyte[i] = reader.ReadByte();
                }
            }
            return imagebyte;
        }
                
        private bool IsCpf(string cpf)
        {
            try
            {
                int[] multiplicador1 = new int[9] { 10, 9, 8, 7, 6, 5, 4, 3, 2 };
                int[] multiplicador2 = new int[10] { 11, 10, 9, 8, 7, 6, 5, 4, 3, 2 };

                cpf = cpf.Trim().Replace(".", "").Replace("-", "");
                if (cpf.Length != 11)
                    return false;

                for (int j = 0; j < 10; j++)
                    if (j.ToString().PadLeft(11, char.Parse(j.ToString())) == cpf)
                        return false;

                string tempCpf = cpf.Substring(0, 9);
                int soma = 0;

                for (int i = 0; i < 9; i++)
                    soma += int.Parse(tempCpf[i].ToString()) * multiplicador1[i];

                int resto = soma % 11;
                if (resto < 2)
                    resto = 0;
                else
                    resto = 11 - resto;

                string digito = resto.ToString();
                tempCpf = tempCpf + digito;
                soma = 0;
                for (int i = 0; i < 10; i++)
                    soma += int.Parse(tempCpf[i].ToString()) * multiplicador2[i];

                resto = soma % 11;
                if (resto < 2)
                    resto = 0;
                else
                    resto = 11 - resto;

                digito = digito + resto.ToString();

                return cpf.EndsWith(digito);
            } catch (Exception)
            {
                return false;
            }
        }

        private void Cpf_Unfocused(object sender, FocusEventArgs e)
        {
            if (!IsCpf(cpf.Text))
            {
                DisplayAlert("Erro", "CPF Inválido", "OK");
                cpf.Focus();
            }
        }

        private async void Bt_imagem_Clicked(object sender, EventArgs e)
        {
            await CrossMedia.Current.Initialize();
            if(!CrossMedia.Current.IsTakePhotoSupported || !CrossMedia.Current.IsCameraAvailable)
            {
                await DisplayAlert("Erro", "Dispositivo sem câmera", "OK");
                return;
            }
            var file = await CrossMedia.Current.TakePhotoAsync(
                new Plugin.Media.Abstractions.StoreCameraMediaOptions
                {
                    SaveToAlbum = true,
                    Directory = "Demo"
                }
             );

            imagem.Source = ImageSource.FromStream(() =>
            {
                var stream = file.GetStream();
                file.Dispose();
                return stream;
            });
        }

        private async void Bt_audio_Clicked(object sender, EventArgs e)
        {
            await RecordAudio();
        }

        async Task RecordAudio() { 
            try
            {
                if (!gravador.IsRecording)
                {
                    await gravador.StartRecording();
                }
                else
                {
                    await gravador.StopRecording();
                }/*
                    gravador.StopRecordingOnSilence = TimeoutSwitch.IsToggled;

                    bt_audio.IsEnabled = false;
                    ReproduzirButton.IsEnabled = false;

                    //Começar gravação
                    var audioRecordTask = await gravador.StartRecording();

                    bt_audio.Text = "Parar";
                    bt_audio.IsEnabled = true;

                    await audioRecordTask;

                    bt_audio.Text = "Gravar";
                    ReproduzirButton.IsEnabled = true;
                } else
                {
                    bt_audio.IsEnabled = false;

                    //parar a gravação...
                    await gravador.StopRecording();

                    bt_audio.IsEnabled = true;
                }
               */
            }
            catch (Exception ex)
            {
                await DisplayAlert("Erro gravador", ex.Message, "OK");
            }
        }
        /*
        public void Finaliza_Reproducao(object sender, EventArgs e)
        {
            ReproduzirButton.IsEnabled = true;
            bt_audio.IsEnabled = true;
        }
        */
        /*
        private async void ReproduzirButton_Clicked(object sender, EventArgs e)
        {
            try
            {
                var filePath = gravador.GetAudioFilePath();

                if (filePath != null)
                {
                    ReproduzirButton.IsEnabled = false;
                    bt_audio.IsEnabled = false;

                    reprodutor.Play(filePath);
                }
            }
            catch (Exception ex)
            {
                await DisplayAlert("Erro", ex.Message, "OK");
            }

        }

        void Finish_Playing(object sender, EventArgs e)
        {
            ReproduzirButton.IsEnabled = true;
            ReproduzirButton.BackgroundColor = Color.FromHex("#7cbb45");
            ReproduzirButton.IsEnabled = true;
            ReproduzirButton.BackgroundColor = Color.FromHex("#7cbb45");
            ReproduzirButton.IsEnabled = false;
            ReproduzirButton.BackgroundColor = Color.Silver;
        }
        */
        private void Pk_cursos_SelectedIndexChanged(object sender, EventArgs e)
        {
            Pk_provas.IsVisible = true;
            Database db = new Database();
            listadisciplinas = db.ListaDiscipliasProvas(listacursos[Pk_cursos.SelectedIndex].idCurso);
            Pk_provas.Items.Clear();
            foreach (DisciplinaProvas c in listadisciplinas)
            {
                if(c.provausada.Equals("Não"))
                    Pk_provas.Items.Add(c.disciplina);
            }
        }

        private void Pk_provas_SelectedIndexChanged(object sender, EventArgs e)
        {
            //DisplayAlert("prova", listadisciplinas[Pk_provas.SelectedIndex].idProva.ToString(), "ok");
            Sl_dados.IsVisible = true;
        }
    }
}