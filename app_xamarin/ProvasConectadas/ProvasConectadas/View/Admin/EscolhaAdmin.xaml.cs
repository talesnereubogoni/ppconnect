using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View.Admin
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class EscolhaAdmin : ContentPage
    {
        public EscolhaAdmin()
        {
            InitializeComponent();
            if (DadosAdmin.tipousuario != 0)
                bt_config.IsVisible = false;
        }

        private void Bt_config_Clicked(object sender, EventArgs e)
        {
            Navigation.PushAsync(new CrudConfig());
        }

        private void Bt_prova_Clicked(object sender, EventArgs e)
        {
            Navigation.PushAsync(new GerenciarProvas());

        }
    }
}