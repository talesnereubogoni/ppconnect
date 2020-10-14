
using Xamarin.Forms;
using Xamarin.Forms.Xaml;
using ProvasConectadas.Model;
using ProvasConectadas.DB;
using System.Collections.Generic;
using System;
using System.IO;
using ProvasConectadas.View.Admin;
using Plugin.Media;
using Plugin.Media.Abstractions;
using Plugin.TextToSpeech;
using System.Text;

namespace ProvasConectadas.View
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class QuestaoView : ContentPage
    {
        string tipoderesposta = "";
        Questao q = new Questao();
        Prova prova;
        List<Alternativas> listadealternativas;
        public bool resp_view=false;

        public QuestaoView(string id, Prova p)
        {
            
            InitializeComponent();
            prova = p;
            Database db = new Database();
            this.q = db.SelecionarQuestao(Convert.ToInt32(id));
            if(q==null)
                DisplayAlert("Erro", "Erro ao abrir a questão!", "ok");
            Title = "Questão " + q.numero;
            if (q.urlvideo!=null)
                bt_video_enunciado.IsVisible = true;

            if (q.tipodaquestao == DadosAdmin.QUESTAOFECHADA)
            {
                listadealternativas = db.SelecionaAlternativas(q);
                //DisplayAlert("ErroMostrarAlternativa", listadealternativas.Count.ToString(), "Cancelar");

                //alternativas.SelectedItem = q.respostafechada;
                alternativas.SelectedItem = q.respostafechadaletra;
                resp_txt.IsVisible = false;

                int x = listadealternativas.Count;
                if (x == 5)
                    c_e.Text = listadealternativas[4].texto;
                if (x >= 4)
                    c_a.Text = listadealternativas[3].texto;
                if (x >= 3)
                    c_b.Text = listadealternativas[4].texto;
                if (x >= 2)
                    c_c.Text = listadealternativas[1].texto;
                if (x >= 1)
                    c_d.Text = listadealternativas[0].texto;

               // DisplayAlert("Atenção", "Aqui", "ok");
                if (listadealternativas[0].urlimagem!=null)
                {
                    char[] chars = Encoding.ASCII.GetChars(listadealternativas[0].urlimagem);
                    int i = 0;
                    string s1 = "";
                    for (; i < chars.Length; i++)
                        s1 += chars[i];
                    /*int compara = 0;
               for (compara = 0; compara < q.urlimagem.Length; compara++)
                   if (s1[compara] != q.urlimagem[compara])
                       break;
               
               Stream stream = new MemoryStream(byteArray);
               c_imagem.Source = (StreamImageSource)ImageSource.FromStream(() => stream);

              */
                    //DisplayAlert("ok", s1, "ok");
                    var byteArray = Convert.FromBase64String(s1);

                    var imgsrc = ImageSource.FromStream(() => new MemoryStream(byteArray)); // exibe a imagem
                    c_imagem.Source = imgsrc;
  //                  String datastr = BitConverter.ToString(listadealternativas[0].urlimagem); // converte para imagem
  //                  c_imagem_a.Source = ImageSource.FromStream(() => new MemoryStream(listadealternativas[0].urlimagem)); // exibe a imagem                        
                }

                if (listadealternativas[0].urlvideo!=null)
                    bt_video_a.IsVisible = true;
                else
                    bt_video_a.IsVisible = false;
            
                

                if (listadealternativas[1].urlimagem != null)
                {
                    String datastr = BitConverter.ToString(listadealternativas[1].urlimagem); // converte para imagem
                    c_imagem_b.Source = ImageSource.FromStream(() => new MemoryStream(listadealternativas[1].urlimagem)); // exibe a imagem

                }
                if (listadealternativas[1].urlvideo != null)
                    bt_video_b.IsVisible = true;
                else
                    bt_video_b.IsVisible = false;

                if (listadealternativas[2].urlimagem != null)
                {
                    String datastr = BitConverter.ToString(listadealternativas[2].urlimagem); // converte para imagem
                    c_imagem_c.Source = ImageSource.FromStream(() => new MemoryStream(listadealternativas[2].urlimagem)); // exibe a imagem

                }
                if (listadealternativas[2].urlvideo != null)
                    bt_video_c.IsVisible = true;
                else
                    bt_video_c.IsVisible = false;

                if (listadealternativas[3].urlimagem != null)
                {
                    String datastr = BitConverter.ToString(listadealternativas[3].urlimagem); // converte para imagem
                    c_imagem_d.Source = ImageSource.FromStream(() => new MemoryStream(listadealternativas[3].urlimagem)); // exibe a imagem

                }
                if (listadealternativas[3].urlvideo != null)
                    bt_video_d.IsVisible = true;
                else
                    bt_video_d.IsVisible = false;

                if (listadealternativas[4].urlimagem != null)
                {
                    String datastr = BitConverter.ToString(listadealternativas[4].urlimagem); // converte para imagem
                    c_imagem_e.Source = ImageSource.FromStream(() => new MemoryStream(listadealternativas[4].urlimagem)); // exibe a imagem

                }
                if (listadealternativas[4].urlvideo != null)
                    bt_video_e.IsVisible = true;
                else
                    bt_video_e.IsVisible = false;
                bt_respostas_view.IsVisible = false;
                stack_alternativas.IsVisible = true;            
            } else
            {
                if (q.tipodaquestao.Equals(DadosAdmin.QUESTAOABERTA))
                {
                    bt_respostas_view.IsVisible = true;
                    stack_alternativas.IsVisible = false;
                    
                    if (q.respostaaberta!=null) {
                        resp_txt.Text = q.respostaaberta;
                        if (q.resposta_view.Equals("Resposta com Imagem"))
                        {
                            resp_img.Source = ImageSource.FromStream(() => new MemoryStream(q.respostaimagem)); // exibe a imagem
                            resp_img.IsVisible = true;
                            resp_txt.IsVisible = false;
                        }
                        else
                        {
                            if (q.resposta_view.Equals("Resposta com Vídeo"))
                            {
                                resp_vid.IsVisible = true;
                                resp_img.IsVisible = false;
                                resp_txt.IsVisible = false;
                                resp_vid.Source = q.urlrespostavideo;
                            }
                            else{
                                if (q.resposta_view.Equals("Resposta com Audio"))
                                {
                                    DisplayAlert("ToDo", "Resposta com audio", "ok");
                                } else
                                {
                                    resp_txt.Text = q.respostaaberta;
                                    resp_txt.IsVisible = true;
                                }
                            }
                        }
                    }
                    
                }
                    
            }
            
            // dados de exibição em tela
            c_enunciado.Text = q.enunciado;

            //mostrar a imagem do enunciado
            if (q.urlimagem!=null && q.urlimagem.Length > 0)
            {
               //string s = "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";
             
                char[] chars = Encoding.ASCII.GetChars(q.urlimagem);
                int i = 0;
                string s1="";
                for (; i < chars.Length; i++)
                    s1 += chars[i];
                
                var byteArray = Convert.FromBase64String(s1);
                /*int compara = 0;
                for (compara = 0; compara < q.urlimagem.Length; compara++)
                    if (s1[compara] != q.urlimagem[compara])
                        break;
                DisplayAlert("ok", compara.ToString() + " " + q.urlimagem.Length + " "+ chars.Length, "ok");
                Stream stream = new MemoryStream(byteArray);
                c_imagem.Source = (StreamImageSource)ImageSource.FromStream(() => stream);
                
               */
                
                var imgsrc = ImageSource.FromStream(() => new MemoryStream(byteArray)); // exibe a imagem
                c_imagem.Source = imgsrc;
                
            }
            else
            {
                grid_imagem_enunciado.Height = 0;
            }           
        }

        private void Bt_confirma_Clicked(object sender, System.EventArgs e)
        {
            try
            {
                if (q.tipodaquestao == DadosAdmin.QUESTAOFECHADA)
                {
                    grid_imagem_enunciado.Height = 0;
                    q.respostafechadaletra = alternativas.SelectedItem.ToString();
                    if (q.respostafechadaletra.Equals("A"))
                        q.respostafechada = listadealternativas[0].id;
                    else
                        if (q.respostafechadaletra.Equals("B"))
                        q.respostafechada = listadealternativas[1].id;
                    else
                            if (q.respostafechadaletra.Equals("C"))
                        q.respostafechada = listadealternativas[2].id;
                    else
                                if (q.respostafechadaletra.Equals("D"))
                        q.respostafechada = listadealternativas[3].id;
                    else
                                    if (q.respostafechadaletra.Equals("E"))
                        q.respostafechada = listadealternativas[4].id;
                    q.resposta_view = q.respostafechadaletra;
                }
                else
                {
                    if (q.tipodaquestao == DadosAdmin.QUESTAOABERTA)
                    {
                        q.respostaaberta = resp_txt.Text;
                        q.resposta_view = q.respostaaberta;
                    }
                }

                if (q.respostaimagem == null)
                { }// DisplayAlert("img", "null", "ok");
                else
                {
                    //DisplayAlert("img", q.respostaimagem.Length.ToString(), "ok");
                    var str = Convert.ToBase64String(q.respostaimagem);
                }

                Database db = new Database();
                db.Atualizar(q);
                App.Current.MainPage = new NavigationPage(new QuestoesDaProva(prova));
            }
            catch (Exception) { }
        }

        private async void Bt_cancela_Clicked(object sender, EventArgs e)
        {
            await Navigation.PopAsync();
        }

        void OnImageNameTapped(object sender, EventArgs args)
        {
            Image img = (Image)sender;
            try
            {
                Navigation.PushAsync(new VerImagem(img.Source));
            }
            catch (Exception ex)
            {
                throw ex;
            }
        }

        private async void Bt_imagem_Clicked(object sender, EventArgs e)
        {
            await CrossMedia.Current.Initialize();
            if (!CrossMedia.Current.IsTakePhotoSupported || !CrossMedia.Current.IsCameraAvailable)
            {
                await DisplayAlert("Erro", "Dispositivo sem câmera", "OK");
                return;
            }
            var file = await CrossMedia.Current.TakePhotoAsync(
                new Plugin.Media.Abstractions.StoreCameraMediaOptions
                {
                    SaveToAlbum = true,
                    Directory = "Imagem",
                    Name = "i" + q.idquestoes_da_prova_gerada.ToString()+".jpg",
                    CompressionQuality = 15
                }
             );
            
            if (file != null)
            {
//                await DisplayAlert("nome", file.Path.ToString(), "ok");
//                byte[] img_bd = ConvertMediaFileToByteArray(file);
//                q.respostaimagem = img_bd;
                q.urlrespostaimagem = file.Path.ToString();
                resp_txt.Text = "Resposta com Imagem";
                q.resposta_view = resp_txt.Text;
                tipoderesposta = "Imagem";
                resp_txt.IsVisible = false;
                resp_img.IsVisible = true;
                resp_vid.IsVisible = false;
                //await DisplayAlert("ok", q.urlrespostaimagem, "ok");
            } else
            {
               //await DisplayAlert("ok", "Cancelou", "ok");
            }

            //Database db = new Database();
            //db.Atualizar(q);
            if (q.urlrespostaimagem != null)
            {
                resp_img.Source = ImageSource.FromFile(q.urlrespostaimagem);// Stream(() => new MemoryStream(q.respostaimagem)); // exibe a imagem                                                                                                    //resp_img.Source = ImageSource.FromStream(() => new MemoryStream(q.respostaimagem)); // exibe a imagem
            }
            //else
            //    await DisplayAlert("ok", "Nulo", "ok");

        }
        private byte[] ConvertMediaFileToByteArray(MediaFile file)
        {
            using (var memoryStream = new MemoryStream())
            {
                file.GetStream().CopyTo(memoryStream);
                return memoryStream.ToArray();
            }
        }

        private void Bt_texto_Clicked(object sender, EventArgs e)
        {
            resp_txt.IsVisible = true;
            resp_txt.Focus();
            resp_img.IsVisible = false;
            tipoderesposta = "Texto";
        }

        private async void Bt_video_Clicked(object sender, EventArgs e)
        {
            tipoderesposta = "Video";
            await CrossMedia.Current.Initialize();
            if (!CrossMedia.Current.IsTakePhotoSupported || !CrossMedia.Current.IsCameraAvailable)
            {
                await DisplayAlert("Erro", "Dispositivo sem câmera", "OK");
                return;
            }
            var file = await CrossMedia.Current.TakeVideoAsync(
                new StoreVideoOptions
                {
                    SaveToAlbum = true,
                    Directory = "Movies",
                    Name = "v" + q.idquestoes_da_prova_gerada.ToString() + ".mp4",
                    Quality = VideoQuality.Low
                }
             );

            if (file != null)
            {
                q.urlrespostavideo = file.Path.ToString();
                resp_txt.Text = "Resposta com Vídeo";
                q.resposta_view = resp_txt.Text;
                tipoderesposta = "Video";
                resp_txt.IsVisible = false;
                resp_vid.IsVisible = true;
                resp_img.IsVisible = false;
                //await DisplayAlert("ok", q.urlrespostavideo, "ok");
            }
            else
            {
                //await DisplayAlert("ok", "Cancelou", "ok");
            }

            if (q.urlrespostavideo != null) 
                resp_vid.Source = q.urlrespostavideo;                
           // else
           //     await DisplayAlert("ok", "Nulo", "ok");
        }

        private void Bt_audio_Clicked(object sender, EventArgs e)
        {
            tipoderesposta = "Audio";

        }
    }
}

    