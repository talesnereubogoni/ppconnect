using Newtonsoft.Json;
using ProvasConectadas.DB;
using ProvasConectadas.Model;
using System;
using System.Collections.Generic;
using System.Collections.ObjectModel;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View.Admin.CadastroTutor
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class ListaTutor : ContentPage
    {
        public ListaTutor()
        {
            InitializeComponent();
            RefreshData();
        }
        private void RefreshData()
        {
            Database db = new Database();
            listatutor.ItemsSource = db.SelecionarTutores();
        }

        private async Task<bool> CarregaDados()
        {
            try { 
                String Conteudo = WebService.WebService.BuscarTutores(DadosAdmin.mac);
                ObservableCollection<Tutor> tutores = JsonConvert.DeserializeObject<ObservableCollection<Tutor>>(Conteudo);
                if (tutores != null)
                {
                    var resp = await DisplayAlert("Confirmação", "Atualizar relação de tutores?", "Sim", "Não");
                    if (resp)
                    {
                        Database db = new Database();
                        db.ApagarTutores();
                        foreach(Tutor t1 in tutores)
                        {
                            /*await DisplayAlert("ok", t1.email+" "+
                                t1.idCurso.ToString()+" "+
                                t1.idUsuario.ToString()+ " "+
                                t1.nome+" "+
                                t1.senha+ " "+ 
                                t1.telefone + " "+
                                t1.tipousuario.ToString(), "ok");*/
                            db.Inserir(t1);
                        }
                    }
                }
                else
                {
                    await DisplayAlert("Erro", "Não existem tutores vinculados aos cursos deste dispositivo!", "OK");
                }
                RefreshData();
            }
            catch (Exception)
            {
                await DisplayAlert("Erro", "Deu erro", "OK");
            }
            return true;
        }

        private async void Bt_atualiza_Clicked(object sender, EventArgs e)
        {
            _ = await CarregaDados();
        }
    }
}