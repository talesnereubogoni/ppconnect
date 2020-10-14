using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

using Xamarin.Forms;
using Xamarin.Forms.Xaml;

namespace ProvasConectadas.View
{
    [XamlCompilation(XamlCompilationOptions.Compile)]
    public partial class VerImagem : ContentPage
    {
        public VerImagem(ImageSource img1)
        {
            InitializeComponent();
            img.Source = img1;
        }
    }
}