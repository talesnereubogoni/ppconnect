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

namespace ProvasConectadas.View.Admin.CadastroCurso
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class ListaCurso : ContentPage
    {

        public ListaCurso()
        {
            InitializeComponent();
            RefreshData();
        }

        public void RefreshData()
        {
            Database db = new Database();
            listacurso.ItemsSource = db.SelecionarCursos();
        }
        private async Task<bool> CarregaDados()
        {
            try
            {
//                await DisplayAlert("CarregarDados", DadosAdmin.mac.ToString(), "Sim", "Não");
                String Conteudo = WebService.WebService.BuscarCursos(DadosAdmin.mac);
                ObservableCollection<Curso> cursos = JsonConvert.DeserializeObject<ObservableCollection<Curso>>(Conteudo);
                if (cursos != null)
                {
                    var resp = await DisplayAlert("Confirmação", "Atualizar dados dos cursos?", "Sim", "Não");
                    if (resp)
                    {
                        Database db = new Database();
                        db.ApagarCursos();
                        for (int i = 0; i < cursos.Count; i++)
                        {
                            db.Inserir(cursos[i]);
                        }
                    }
                }
                else
                {
                    await DisplayAlert("Erro", "Não existem cursos vinculados ao dispositivo!", "OK");
                }
                RefreshData();
            }catch (Exception)
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